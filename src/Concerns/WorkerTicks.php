<?php


namespace Z1px\Http\Concerns;


use Swoole\Http\Server;

trait WorkerTicks
{

    /**
     * workerStartTicks
     *
     * @param Server|mixed $server
     * @return bool
     */
    public function workerStartTicks(Server $server)
    {
        if (!$server->taskworker) {
            return false;
        }

        $enable = $this->container->make('config')->get('swoole_http.timer.enable', false);
        if (!$enable) {
            return false;
        }

        $ticks = $this->container->make('config')->get('swoole_http.timer.ticks', []);
        if (empty($ticks)) {
            return false;
        }

        $worker_num = $this->container->make('config')->get('swoole_http.server.options.worker_num', swoole_cpu_num());
        $task_worker_num = $this->container->make('config')->get('swoole_http.server.options.task_worker_num', swoole_cpu_num());
        $min_taskworker_id = $worker_num;
        $max_taskworker_id = $min_taskworker_id + $task_worker_num - 1;

        foreach ($ticks as $tick) {
            is_null($tick['worker_id']) && $tick['worker_id'] = $max_taskworker_id;
            if ($tick['worker_id'] === $server->worker_id) {
                $server->tick($tick['interval'], $tick['job'], $server, ...$tick['params']);
            }
        }
        return true;
    }

}