<?php

namespace Subdesign\LaravelCliUser;

use Illuminate\Support\ServiceProvider;
use Subdesign\LaravelCliUser\Commands\CliUserCreateCommand;
use Subdesign\LaravelCliUser\Commands\CliUserDeleteCommand;
use Subdesign\LaravelCliUser\Commands\CliUserListCommand;

/**
 * Laravel CLI User - Service Provider
 *
 * @author Barna Szalai <szalai.b@gmail.com>
 * 
 */
class CliUserServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        $this->publishes([
            __DIR__.'/../config/cliuser.php' => config_path('cliuser.php'),
        ], 'config');
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {   
        $this->app->bind('command.cliuser.create', CliUserCreateCommand::class);
        $this->app->bind('command.cliuser.delete', CliUserDeleteCommand::class);
        $this->app->bind('command.cliuser.list', CliUserListCommand::class);

        $this->commands([
            'command.cliuser.create',
            'command.cliuser.delete',
            'command.cliuser.list'
        ]);
    }
}
