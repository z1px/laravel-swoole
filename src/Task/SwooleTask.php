<?php


namespace Z1px\Http\Task;


use Illuminate\Contracts\Container\Container;
use Illuminate\Queue\Jobs\JobName;
use Swoole\Http\Server;

class SwooleTask
{
    /**
     * The IoC container instance.
     *
     * @var \Illuminate\Container\Container
     */
    protected $container;

    /**
     * The Swoole Server instance.
     *
     * @var \Swoole\Http\Server
     */
    protected $server;

    /**
     * The Swoole payload.
     *
     * @var array
     */
    protected $payload;

    /**
     * Create a new job instance.
     *
     * @param \Illuminate\Contracts\Container\Container $container
     * @param  \Swoole\Http\Server $server
     * @param array  $payload
     */
    public function __construct(Container $container, Server $server, array $payload)
    {
        $this->container = $container;
        $this->server = $server;
        $this->payload = $payload;
    }

    /**
     * Fire the job.
     *
     * @return void
     */
    public function fire()
    {
        if (method_exists($this, 'resolveAndFire')) {
            $this->resolveAndFire($this->payload);
        } else {
            [$class, $method] = JobName::parse($this->payload['job']);

            ($this->resolve($class))->{$method}($this, $this->payload['data']);
        }
    }

    /**
     * Resolve the given class.
     *
     * @param  string  $class
     * @return mixed
     */
    protected function resolve($class)
    {
        return $this->container->make($class);
    }
}