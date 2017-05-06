<?php

namespace Subdesign\LaravelCliUser\Commands;

use Illuminate\Console\Command;
use Illuminate\Contracts\Config\Repository as Config;
use Exception;
use Subdesign\LaravelCliUser\Common\Checker;

/**
 * Laravel CLI User - Delete user
 *
 * @author Barna Szalai <szalai.b@gmail.com>
 * 
 */
class CliUserDeleteCommand extends Command
{
    protected $config;

    protected $checker;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'cliuser:delete {source : ID or email}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Delete user';

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

        try {

            if (is_numeric(trim($this->argument('source')))) {
                $model::findOrFail($this->argument('source'))->delete();
            } elseif (filter_var(trim($this->argument('source')), FILTER_VALIDATE_EMAIL)) {
                $model::where('email', trim($this->argument('source')))->delete();
            } else {
                $this->error('The entered value isn\'t an ID or email!');
                exit;
            }
            
        } catch (Exception $e) {
            $this->error($e->getMessage());
            exit;
        }

        $this->info('USER DELETED.');
    }
}
