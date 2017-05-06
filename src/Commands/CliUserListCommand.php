<?php

namespace Subdesign\LaravelCliUser\Commands;

use Illuminate\Console\Command;
use Illuminate\Contracts\Config\Repository as Config;
use Subdesign\LaravelCliUser\Common\Checker;

/**
 * Laravel CLI User - List users
 *
 * @author Barna Szalai <szalai.b@gmail.com>
 * 
 */
class CliUserListCommand extends Command
{
    protected $config;

    protected $checker;
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'cliuser:list';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'List users';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct(Config $config, Checker $checker)
    {
        parent::__construct();

        $this->config = $config;

        $this->checker = $checker;
    }    

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $this->info("   __                           _     ___ _ _                        ");
        $this->info("  / /  __ _ _ __ __ ___   _____| |   / __\ (_)  /\ /\  ___  ___ _ __ ");
        $this->info(" / /  / _` | '__/ _` \ \ / / _ \ |  / /  | | | / / \ \/ __|/ _ \ '__|");
        $this->info("/ /__| (_| | | | (_| |\ V /  __/ | / /___| | | \ \_/ /\__ \  __/ |   ");
        $this->info("\____/\__,_|_|  \__,_| \_/ \___|_| \____/|_|_|  \___/ |___/\___|_| ");
        $this->info("");
        
        if ( ! $this->checker->checkConfig()) {
            $this->error(PHP_EOL.'ERROR: Config file is missing! Did you run the vendor:publish command?');
            exit;
        }

        if ( ! $model = $this->config->get('cliuser.model')) {
            $this->error(PHP_EOL.'ERROR: The class '.$model.' is not an Eloquent model!');
            exit;
        }

        if ( ! $this->checker->checkModel($model)) {
            $this->error(PHP_EOL.'ERROR: The class '.$model.' is not an Eloquent model!');
            exit;
        }

        $users = $model::all(['id', 'name', 'email', 'created_at', 'updated_at'])->toArray();

        $headers = ['ID', 'Name', 'E-mail', 'Created at', 'Updated at'];

        $this->table($headers, $users);

        if (count($users) == 0) {
            $this->warn('No users found!');
        }
    }
}
