<?php

namespace Subdesign\LaravelCliUser\Common;

use Illuminate\Contracts\Config\Repository as Config;

/**
 * Checker methods
 *
 * @author Barna Szalai <szalai.b@gmail.com>
 */
class Checker {

    /**
     * config 
     * 
     * @var object
     */
    protected $config;

    public function __construct(Config $config)
    {
        $this->config = $config;
    }

    /**
     * Check if config file exists
     * 
     * @return void
     */
    public function checkConfig()
    {
        if ( ! $this->config->has('cliuser.model')) {            
            return false;
        }

        return true;
    }

    /**
     * Check if object is an Eloquent model
     * 
     * @param  string $user
     * @return void
     */
    public function checkModel($user)
    {
        $model = new $user;    

        if ( ! $model instanceof \Illuminate\Database\Eloquent\Model) {            
            return false;
        }

        return true;
    }
}

