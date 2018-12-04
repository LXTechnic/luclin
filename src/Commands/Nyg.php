<?php

namespace Luclin\Commands;

use Illuminate\Console\Command;
use File;
use Artisan;

class Nyg extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'luc:nyg';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Just run it.';

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
        echo '

$$\   $$\                               $$\     $$\
$$$\  $$ |                              \$$\   $$  |
$$$$\ $$ | $$$$$$\  $$\  $$\  $$\        \$$\ $$  /$$$$$$\   $$$$$$\   $$$$$$\
$$ $$\$$ |$$  __$$\ $$ | $$ | $$ |        \$$$$  /$$  __$$\  \____$$\ $$  __$$\
$$ \$$$$ |$$$$$$$$ |$$ | $$ | $$ |         \$$  / $$$$$$$$ | $$$$$$$ |$$ |  \__|
$$ |\$$$ |$$   ____|$$ | $$ | $$ |          $$ |  $$   ____|$$  __$$ |$$ |
$$ | \$$ |\$$$$$$$\ \$$$$$\$$$$  |          $$ |  \$$$$$$$\ \$$$$$$$ |$$ |
\__|  \__| \_______| \_____\____/           \__|   \_______| \_______|\__|



 $$$$$$\                                 $$\     $$\
$$  __$$\                                $$ |    \__|
$$ /  \__| $$$$$$\   $$$$$$\   $$$$$$\ $$$$$$\   $$\ $$$$$$$\   $$$$$$\   $$$$$$$\
$$ |$$$$\ $$  __$$\ $$  __$$\ $$  __$$\\_$$  _|  $$ |$$  __$$\ $$  __$$\ $$  _____|
$$ |\_$$ |$$ |  \__|$$$$$$$$ |$$$$$$$$ | $$ |    $$ |$$ |  $$ |$$ /  $$ |\$$$$$$\
$$ |  $$ |$$ |      $$   ____|$$   ____| $$ |$$\ $$ |$$ |  $$ |$$ |  $$ | \____$$\
\$$$$$$  |$$ |      \$$$$$$$\ \$$$$$$$\  \$$$$  |$$ |$$ |  $$ |\$$$$$$$ |$$$$$$$  |
 \______/ \__|       \_______| \_______|  \____/ \__|\__|  \__| \____$$ |\_______/
                                                               $$\   $$ |
                                                               \$$$$$$  |
                                                                \______/

';
    }

}
