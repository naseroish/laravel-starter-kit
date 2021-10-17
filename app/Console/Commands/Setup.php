<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class Setup extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'setup';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'A command to setup the project.';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        // 1. Setup Salla App Settings
        $client_id = $this->ask('Client ID');

        if (!empty($client_id)) {
            $this->writeNewEnvironmentFileWith($client_id, 'SALLA_OAUTH_CLIENT_ID');
        }

        $client_secret = $this->ask('Client Secret');
        if (!empty($client_secret)) {
            $this->writeNewEnvironmentFileWith($client_secret, 'SALLA_OAUTH_CLIENT_SECRET');
        }

        $webhook_secret = $this->ask('Webhook Secret');
        if (!empty($webhook_secret)) {
            $this->writeNewEnvironmentFileWith($webhook_secret, 'SALLA_WEBHOOK_SECRET');
        }

        $this->call('key:generate');
        $this->call('migrate');

        $this->info('You are ready 🎉 run `php artisan serve`');
    }

    /**
     * Write a new environment file with the given key.
     *
     * @param $value
     * @param  string  $key
     *
     * @return void
     */
    protected function writeNewEnvironmentFileWith($value, string $key = 'APP_KEY')
    {
        file_put_contents($this->laravel->environmentFilePath(), preg_replace(
            $this->keyReplacementPattern($key),
            $key.'='.$value,
            file_get_contents($this->laravel->environmentFilePath())
        ));
    }

    /**
     * Get a regex pattern that will match env APP_KEY with any random key.
     *
     * @return string
     */
    protected function keyReplacementPattern(string $key = 'APP_KEY')
    {
        return "/^{$key}=(.*)/m";
    }
}
