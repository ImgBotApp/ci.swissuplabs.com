<?php

namespace App\Console\Commands;

use App\Lib\Terminal;
use Illuminate\Console\Command;

class ComposerConfig extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'composer:config';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Copy github token from .env into composer global config';

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
        $command = 'composer config -g github-oauth.github.com';

        $output = Terminal::exec($command);

        if (strpos($output, 'github-oauth.github.com is not defined') !== false) {
            Terminal::exec($command . ' ' . config('github.token'));

            $this->info("Done.");
        } else {
            $this->info("Composer's token is already set.");
        }
    }
}
