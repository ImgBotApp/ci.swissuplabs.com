<?php

namespace App\Console\Commands;

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
        $storage = Storage::disk('local');
        foreach ($group as $code => $values) {
            if (empty($values['active'])) {
                continue;
            }

            $archive = 'tools/__archive/' . $values['repository'] . '-' . $values['ref'] . '.tar';

            if ($storage->exists($archive) && !$this->option('force')) {
                continue;
            }

            $storage->put(
                $archive,
                Github::api('repo')->contents()->archive(
                    $values['username'],
                    $values['repository'],
                    'tarball',
                    $values['ref']
                )
            );

            $destination = 'tools/' . $code;
            $storage->deleteDirectory($destination);
            $storage->makeDirectory($destination);

            $command = sprintf(
                "tar -xf %s --directory %s --strip-components=1",
                storage_path("app/{$archive}"),
                storage_path("app/{$destination}")
            );
            Terminal::exec($command);

            if (empty($values['postinstall'])) {
                continue;
            }

            foreach ($values['postinstall'] as $command) {
                Terminal::exec($command);
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

            $input = $this->secret($values['prompt']);

            if (!$this->setEnvironmentFileValue($values['env_key'], $input)) {
                return;
            }

            $this->laravel['config'][$key] = $input;

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
