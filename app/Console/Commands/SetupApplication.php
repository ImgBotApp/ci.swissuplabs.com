<?php

namespace App\Console\Commands;

use Carbon\Carbon;
use App\Lib\Github;
use App\Lib\Terminal;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class SetupApplication extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:setup
                            {--force : Whether the tools must be redownloaded}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Setup application preferences';

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
        $settings = config('setup');
        foreach ($settings as $groupName => $group) {
            $method = 'process' . ucfirst($groupName);

            try {
                $this->{$method}($group);
            } catch (\Exception $e) {
                $this->error($e->getMessage());
                break;
            }
        }
        $this->info('Setup is successfully completed.');
    }

    /**
     * Download and unpack tools into app/tools folder
     *
     * @param  array $group
     * @return void
     */
    private function processTools($group)
    {
        foreach ($group as $downloader => $tools) {
            $downloader = ucfirst($downloader);
            $downloader = "\\App\\Downloader\\{$downloader}";
            $downloader = new $downloader;

            foreach ($tools as $code => $values) {
                if (empty($values['active'])) {
                    continue;
                }

                $flag = 'tools/__installed/' . $code;
                if (isset($values['ref'])) {
                    $flag .= '.' . $values['ref'];
                }
                if (Storage::exists($flag) && !$this->option('force')) {
                    continue;
                }

                $downloader->download($values, 'tools/' . $code);

                if (!empty($values['postinstall'])) {
                    foreach ($values['postinstall'] as $command) {
                        Terminal::exec($command);
                    }
                }

                if (!Storage::exists($flag)) {
                    Storage::put($flag, Carbon::now()->toDateTimeString());
                }
            }
        }
    }

    /**
     * Adds a new row into .env file if config does not have it yet
     *
     * @param  array $group
     * @return void
     */
    private function processConfig($group)
    {
        foreach ($group as $key => $values) {
            if (config($key)) {
                continue;
            }

            $method = $values['method'];
            $input = $this->{$method}($values['prompt']);

            if (!$this->setEnvironmentFileValue($values['env_key'], $input)) {
                return;
            }

            $this->laravel['config'][$key] = $input;

            if (!empty($values['postinstall'])) {
                foreach ($values['postinstall'] as $command) {
                    $this->callSilent($command);
                }
            }

            $this->info("Done.");
        }
    }

    /**
     * Write a new config value into .env file
     *
     * @param string $key   [description]
     * @param string $value [description]
     * @return int|false
     */
    private function setEnvironmentFileValue($key, $value)
    {
        return file_put_contents(
            $this->laravel->environmentFilePath(),
            "\n" . $key . '=' . $value,
            FILE_APPEND | LOCK_EX
        );
    }
}
