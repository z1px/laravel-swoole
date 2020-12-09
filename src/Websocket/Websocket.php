<?php

namespace Z1px\Http\Websocket;

use Illuminate\Contracts\Container\Container;
use Illuminate\Contracts\Pipeline\Pipeline as PipelineContract;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Config;
use InvalidArgumentException;
use Z1px\Http\Server\Facades\Server;
use Z1px\Http\Server\Manager;
use Z1px\Http\Task\SwooleTask;
use Z1px\Http\Websocket\Rooms\RoomContract;

/**
 * Class Websocket
 */
class Websocket
{
    use Authenticatable;

    const PUSH_ACTION = 'push';
    const EVENT_CONNECT = 'connect';
    const USER_PREFIX = 'uid_';
    const TASK_ACTION = 'task';

    /**
     * Determine if to broadcast.
     *
     * @var boolean
     */
    protected $isBroadcast = false;

    /**
     * Scoket sender's fd.
     *
     * @var integer
     */
    protected $sender;

    /**
     * Recepient's fd or room name.
     *
     * @var array
     */
    protected $to = [];

    /**
     * Websocket event callbacks.
     *
     * @var array
     */
    protected $callbacks = [];

    /**
     * Middleware for on connect request.
     *
     * @var array
     */
    protected $middleware = [];

    /**
     * Pipeline instance.
     *
     * @var \Illuminate\Contracts\Pipeline\Pipeline
     */
    protected $pipeline;

    /**
     * Room adapter.
     *
     * @var \Z1px\Http\Websocket\Rooms\RoomContract
     */
    protected $room;

    /**
     * DI Container.
     *
     * @var \Illuminate\Contracts\Container\Container
     */
    protected $container;

    /**
     * Websocket constructor.
     *
     * @param \Z1px\Http\Websocket\Rooms\RoomContract $room
     * @param \Illuminate\Contracts\Pipeline\Pipeline $pipeline
     */
    public function __construct(RoomContract $room, PipelineContract $pipeline)
    {
        $this->room = $room;
        $this->setPipeline($pipeline);
        $this->setDefaultMiddleware();
    }

    /**
     * Set broadcast to true.
     */
    public function broadcast(): self
    {
        $this->isBroadcast = true;

        return $this;
    }

    /**
     * Set multiple recipients fd or room names.
     *
     * @param integer, string, array
     *
     * @return $this
     */
    public function to($values): self
    {
        $values = is_string($values) || is_integer($values) ? func_get_args() : $values;

        foreach ($values as $value) {
            if (! in_array($value, $this->to)) {
                $this->to[] = $value;
            }
        }

        return $this;
    }

    /**
     * Join sender to multiple rooms.
     *
     * @param string, array $rooms
     *
     * @return $this
     */
    public function join($rooms): self
    {
        $rooms = is_string($rooms) || is_integer($rooms) ? func_get_args() : $rooms;

        $this->room->add($this->sender, $rooms);

        return $this;
    }

    /**
     * Make sender leave multiple rooms.
     *
     * @param array $rooms
     *
     * @return $this
     */
    public function leave($rooms = []): self
    {
        $rooms = is_string($rooms) || is_integer($rooms) ? func_get_args() : $rooms;

        $this->room->delete($this->sender, $rooms);

        return $this;
    }

    /**
     * Emit data and reset some status.
     *
     * @param string
     * @param mixed
     *
     * @return boolean
     */
    public function emit(string $event, $data): bool
    {
        $fds = $this->getFds();
        $assigned = ! empty($this->to);

        // if no fds are found, but rooms are assigned
        // that means trying to emit to a non-existing room
        // skip it directly instead of pushing to a task queue
        if (empty($fds) && $assigned) {
            $this->reset();
            return false;
        }

        $payload = [
            'opcode'    => Config::get('swoole_http.server.opcode', WEBSOCKET_OPCODE_BINARY),
            'sender'    => $this->sender,
            'fds'       => $fds,
            'broadcast' => $this->isBroadcast,
            'assigned'  => $assigned,
            'event'     => $event,
            'message'   => $data,
        ];

        $result = true;
        $server = App::make(Server::class);
        if ($server->taskworker) {
            App::make(Manager::class)->pushMessage($server, $payload);
        } else {
            $result = $server->task([
                'action' => static::PUSH_ACTION,
                'data' => $payload
            ]);
        }

        $this->reset();

        return $result !== false;
    }

    /**
     * Handle something on task worker and reset some status.
     *
     * @param      $job
     * @param      $data
     * @param null $worker_id
     * @access
     * @return bool
     */
    public function task($job, $data, $worker_id = null): bool
    {
        $payload = [
            'sender'    => $this->sender,
            'job'       => $job,
            'data'      => $data,
        ];

        $result = true;
        $server = App::make(Server::class);
        if ($server->taskworker) {
            (new SwooleTask($this->container, $server, $payload))->fire();
        } else {
            $result = $server->task([
                'action' => static::TASK_ACTION,
                'data' => $payload
            ], is_null($worker_id) ? $server->worker_id : $worker_id);
        }

        $this->reset();

        return $result !== false;
    }

    /**
     * Handle something on current worker and reset some status.
     *
     * @param
     * @param mixed
     *
     * @return boolean
     */
    public function work($job, $data): bool
    {
        $payload = [
            'sender'    => $this->sender,
            'job'       => $job,
            'data'      => $data,
        ];

        $result = true;
        $server = App::make(Server::class);
        if ($server->taskworker) {
            $container = $this->container;
        } else {
            $container = new \Illuminate\Container\Container();
        }
        (new SwooleTask($container, $server, $payload))->fire();

        $this->reset();

        return $result !== false;
    }

    /**
     * An alias of `join` function.
     *
     * @param string
     *
     * @return $this
     */
    public function in($room)
    {
        $this->join($room);

        return $this;
    }

    /**
     * Register an event name with a closure binding.
     *
     * @param string
     * @param callback
     *
     * @return $this
     */
    public function on(string $event, $callback)
    {
        if (! is_string($callback) && ! is_callable($callback)) {
            throw new InvalidArgumentException(
                'Invalid websocket callback. Must be a string or callable.'
            );
        }

        $this->callbacks[$event] = $callback;

        return $this;
    }

    /**
     * Check if this event name exists.
     *
     * @param string
     *
     * @return boolean
     */
    public function eventExists(string $event)
    {
        return array_key_exists($event, $this->callbacks);
    }

    /**
     * Execute callback function by its event name.
     *
     * @param string
     * @param mixed
     *
     * @return mixed
     */
    public function call(string $event, $data = null)
    {
        if (! $this->eventExists($event)) {
            return null;
        }

        // inject request param on connect event
        $isConnect = $event === static::EVENT_CONNECT;
        $dataKey = $isConnect ? 'request' : 'data';

        // dispatch request to pipeline if middleware are set
        if ($isConnect && count($this->middleware)) {
            $data = $this->setRequestThroughMiddleware($data);
        }

        return App::call($this->callbacks[$event], [
            'websocket' => $this,
            $dataKey => $data,
        ]);
    }

    /**
     * Close current connection.
     *
     * @param integer
     *
     * @return boolean
     */
    public function close(int $fd = null)
    {
        return App::make(Server::class)->close($fd ?: $this->sender);
    }

    /**
     * Set sender fd.
     *
     * @param integer
     *
     * @return $this
     */
    public function setSender(int $fd)
    {
        $this->sender = $fd;

        return $this;
    }

    /**
     * Get current sender fd.
     */
    public function getSender()
    {
        return $this->sender;
    }

    /**
     * Get broadcast status value.
     */
    public function getIsBroadcast()
    {
        return $this->isBroadcast;
    }

    /**
     * Get push destinations (fd or room name).
     */
    public function getTo()
    {
        return $this->to;
    }

    /**
     * Get all fds we're going to push data to.
     */
    protected function getFds()
    {
        $fds = array_filter($this->to, function ($value) {
            return is_integer($value);
        });
        $rooms = array_diff($this->to, $fds);

        foreach ($rooms as $room) {
            $clients = $this->room->getClients($room);
            // fallback fd with wrong type back to fds array
            if (empty($clients) && is_numeric($room)) {
                $fds[] = $room;
            } else {
                $fds = array_merge($fds, $clients);
            }
        }

        return array_values(array_unique($fds));
    }

    /**
     * Reset some data status.
     *
     * @param bool $force
     *
     * @return $this
     */
    public function reset($force = false)
    {
        $this->isBroadcast = false;
        $this->to = [];

        if ($force) {
            $this->sender = null;
            $this->userId = null;
        }

        return $this;
    }

    /**
     * Get or set middleware.
     *
     * @param array|string|null $middleware
     *
     * @return array|\Z1px\Http\Websocket\Websocket
     */
    public function middleware($middleware = null)
    {
        if (is_null($middleware)) {
            return $this->middleware;
        }

        if (is_string($middleware)) {
            $middleware = func_get_args();
        }

        $this->middleware = array_unique(array_merge($this->middleware, $middleware));

        return $this;
    }

    /**
     * Set default middleware.
     */
    protected function setDefaultMiddleware()
    {
        $this->middleware = Config::get('swoole_websocket.middleware', []);
    }

    /**
     * Set container to pipeline.
     *
     * @param \Illuminate\Contracts\Container\Container $container
     *
     * @return $this
     */
    public function setContainer(Container $container)
    {
        $pipeline = $this->pipeline;

        $closure = function () use ($container) {
            $this->container = $container;
        };

        $resetPipeline = $closure->bindTo($pipeline, $pipeline);
        $resetPipeline();

        return $this;
    }

    /**
     * Set pipeline.
     *
     * @param \Illuminate\Contracts\Pipeline\Pipeline $pipeline
     *
     * @return $this
     */
    public function setPipeline(PipelineContract $pipeline)
    {
        $this->pipeline = $pipeline;

        return $this;
    }

    /**
     * Get pipeline.
     */
    public function getPipeline()
    {
        return $this->pipeline;
    }

    /**
     * Set the given request through the middleware.
     *
     * @param  \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\Request
     */
    protected function setRequestThroughMiddleware($request)
    {
        return $this->pipeline
            ->send($request)
            ->through($this->middleware)
            ->then(function ($request) {
                return $request;
            });
    }
}
