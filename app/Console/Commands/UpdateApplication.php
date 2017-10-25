<?php

namespace App\Console\Commands;

use App;
use App\Lib\Terminal;
use Illuminate\Console\Command;

class UpdateApplication extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:update';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update application sources';

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
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $command = implode(' && ', [
            'cd ' . App::basePath(),
            'php artisan down',
            'git pull',
            'composer install',
            'php artisan config:clear',
            'php artisan view:clear',
            'php artisan cache:clear',
            'php artisan migrate --force',
            'php artisan app:setup',
            'php artisan config:cache',
            'php artisan queue:restart',
            'php artisan up',
        ]);

        try {
            $this->info(Terminal::exec($command));
        } catch (\Exception $e) {
            $this->error($e->getMessage());
        }
    }
}
