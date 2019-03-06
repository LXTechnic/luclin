<?php

namespace Luclin\Commands\Queue;

use Illuminate\Queue\Worker;
use Illuminate\Queue\Console\WorkCommand;
use Illuminate\Contracts\Queue\Factory as QueueFactoryContract;

/**
 * update for modules
 */
class Work extends WorkCommand
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'luc:queue:work
                            {connection? : The name of the queue connection to work}
                            {--queue= : The names of the queues to work}
                            {--daemon : Run the worker in daemon mode (Deprecated)}
                            {--once : Only process the next job on the queue}
                            {--stop-when-empty : Stop when the queue is empty}
                            {--delay=0 : The number of seconds to delay failed jobs}
                            {--force : Force the worker to run even in maintenance mode}
                            {--memory=128 : The memory limit in megabytes}
                            {--sleep=3 : Number of seconds to sleep when no job is available}
                            {--timeout=60 : The number of seconds a child process can run}
                            {--tries=0 : Number of times to attempt a job before logging it failed}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '队列监听执行';

    public function __construct(Worker $worker)
    {
        parent::__construct($worker);

        $this->worker->setManager(resolve(QueueFactoryContract::class));
    }

}
