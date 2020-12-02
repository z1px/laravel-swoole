<?php

namespace Z1px\Http\Task\Connectors;

use Illuminate\Queue\Connectors\ConnectorInterface;
use Z1px\Http\Helpers\FW;
use Z1px\Http\Task\QueueFactory;

/**
 * Class SwooleTaskConnector
 */
class SwooleTaskConnector implements ConnectorInterface
{
    /**
     * Swoole Server Instance
     *
     * @var \Swoole\Http\Server
     */
    protected $swoole;

    /**
     * Create a new Swoole Async task connector instance.
     *
     * @param  \Swoole\Http\Server $swoole
     *
     * @return void
     */
    public function __construct($swoole)
    {
        $this->swoole = $swoole;
    }

    /**
     * Establish a queue connection.
     *
     * @param  array $config
     *
     * @return \Illuminate\Contracts\Queue\Queue
     */
    public function connect(array $config)
    {
        return QueueFactory::make($this->swoole, FW::version());
    }
}
