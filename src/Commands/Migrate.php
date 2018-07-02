<?php

namespace Luclin\Commands;

use Illuminate\Console\Command;

class Migrate extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'luc:migrate {action} {--module=} {--path=database/migrations}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '数据结构迁移';

    private $mapping = [
        'up'    => 'migrate',
        'down'  => 'migrate:rollback',
    ];

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Perform the operation.
     *
     * @return void
     */
    public function handle()
    {
        [
            'action'    => $action,
        ] = $this->arguments();
        @[
            'module'    => $module,
            'path'      => $path,
        ] = $this->options();

        $action = $this->mapping[$action];
        $params = [];
        $module && $path && $params['--path'] = \luc\mod($module)->path($path);
        $this->call($action, $params);
    }

}
