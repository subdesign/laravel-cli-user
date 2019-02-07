<?php 

namespace Subdesign\LaravelCliUser\Commands;

use Exception;
use Illuminate\Console\Command;
use Subdesign\LaravelCliUser\Common\Checker;
use Illuminate\Contracts\Config\Repository as Config;
use Illuminate\Contracts\Filesystem\Filesystem as File;
use Illuminate\Contracts\Hashing\Hasher;
use Illuminate\Contracts\Validation\Factory as Validator;

/**
 * Laravel CLI User - Create user
 *
 * @author Barna Szalai <szalai.b@gmail.com>
 * 
 */
class CliUserCreateCommand extends Command
{
    protected $checker;

    protected $config;

    protected $file;

    protected $validator;

    protected $hasher;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'cliuser:create {name?} {email?} {--random-password : Create the user with a random password} {--show-password : Show letters in password field}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Quickly create user on console';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct(Checker $checker, Config $config, File $file, Validator $validator, Hasher $hasher)
    {
        parent::__construct();

        $this->config = $config;

        $this->file = $file;

        $this->validator = $validator;

        $this->hasher = $hasher;

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

        $fullname = $this->getFullname();

        $email = $this->getEmail();

        if ($this->option('random-password')) {

            $password = str_random(40);

            if ($this->option('show-password')) {
                $this->info('Generated password: ' . $password . PHP_EOL);
            }

        } else {
            if ($this->confirm('Do you want to set password?')) {

                $password = $this->getPassword();

                $password_confirmation = $this->getPasswordConfirmation();

                if ($password !== $password_confirmation) {
                    $this->error('The password confirmation doesn\'t match the password!');
                    $this->getPasswordConfirmation();
                }

            } else {
                $password = null;
            }
        }

        try {

            $this->saveUser($model, $fullname, $email, $password); 
            
            $this->info('USER CREATED.');
                        
        } catch (Exception $e) {
            
            $this->error($e->getMessage());
        }
    }

    /**
     * Get fullname from console and validate
     * 
     * @return string fullname
     */
    private function getFullname()
    {
        $value = $this->argument('name');

        if (empty($value) === true) {
            $value = $this->ask('Full name');
        }

        $v = $this->validator->make(
            ['value' => trim($value)], 
            ['value' => 'required|regex:/^[\pL\s]+$/u'], 
            ['value.required' => 'Please enter the name!', 'value.regex' => 'The name may only contain letters and spaces!']
        );

        if ($v->fails()) {
            $this->error(implode("\n", $v->errors()->all()));
            return $this->getFullname();
        }

        return $value;
    }

    /**
     * Get email from console and validate
     * 
     * @return string email
     */
    private function getEmail()
    {

        $value = $this->argument('email');

        if (empty($value) === true) {
            $value = $this->ask('E-mail');
        }

        $v = $this->validator->make(
            ['value' => trim($value)], 
            ['value' => 'required|email'], 
            ['value.required' => 'Please enter the email!', 'value.email' => 'Invalid email format!']
        );
        
        if ($v->fails()) {
            $this->error(implode("\n", $v->errors()->all()));
            return $this->getEmail();
        } 
        
        return $value;
    }

    /**
     * Get password from console and validate
     * 
     * @return string password
     */
    private function getPassword()
    {
        if ($this->option('show-password')) {
            $value = $this->ask('Password');
        } else {
            $value = $this->secret('Password');            
        }

        $v = $this->validator->make(
            ['value' => trim($value)], 
            ['value' => 'required'], 
            ['value.required' => 'Please enter the password!']
        );
        
        if ($v->fails()) {
            $this->error(implode("\n", $v->errors()->all()));
            return $this->getPassword();
        }

        return $value;
    }

    /**
     * Get password confirmation from console and validate
     * 
     * @return string password confirmation
     */
    private function getPasswordConfirmation()
    {
        if ($this->option('show-password')) {
            $value = $this->ask('Confirm password');
        } else {
            $value = $this->secret('Confirm password');
        }

        $v = $this->validator->make(
            ['value' => trim($value)], 
            ['value' => 'required'], 
            ['value.required' => 'Please enter the confirmed password!']
        );
        
        if ($v->fails()) {
            $this->error(implode("\n", $v->errors()->all()));
            return $this->getPasswordConfirmation();
        }

        return $value;
    }

    /**
     * Save user to DB
     * 
     * @param  string $model    
     * @param  string $fullname 
     * @param  string $email    
     * @param  string|null $password 
     * @return void
     */
    private function saveUser($model, $fullname, $email, $password)
    {
        $user = new $model;

        $user->name = $fullname;
        $user->email = $email;

        if ( ! is_null($password)) {
            $user->password = $this->hasher->make($password);
        }

        if ( ! $user->save()) {
            throw new Exception("Error on saving user!");            
        }
    }
}
