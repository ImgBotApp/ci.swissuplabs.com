<?php
namespace App\Tests;

use Symfony\Component\Process\Process;

class Composer extends Test
{
    public function getTitle()
    {
        return 'composer validate';
    }

    /**
     * @return boolean
     */
    public function canRun()
    {
        return file_exists($this->getPath() . '/composer.json');
    }

    /**
     * Run the test and return console output.
     * If the test was successfull, result will be an empty string.
     *
     * @return string
     */
    public function run()
    {
        $command = implode(' && ', [
            sprintf(
                "composer validate %s",
                escapeshellarg($this->getPath() . '/composer.json')
            )
        ]);

        $process = new Process($command);
        $process->run();

        if (!$process->isSuccessful() && $process->getErrorOutput()) {
            return $process->getErrorOutput();
        }

        return '';
    }
}
