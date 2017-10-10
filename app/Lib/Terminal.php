<?php

namespace App\Lib;

use Symfony\Component\Process\Process;

class Terminal
{
    /**
     * Runs command with symfony's process class.
     *
     * @param  string  $command
     * @return string
     * @throws Exception
     */
    public static function exec($command)
    {
        $process = new Process($command, null, null, null, 600);
        $process->run();

        // phpcs uses exit(1) if validation errors where found, so in order
        // to detect if it was really a terminal error - checkout error_output too
        if (!$process->isSuccessful() && $process->getErrorOutput()) {
            throw new \Exception(sprintf(
                'Input: %s; Output: %s',
                $command,
                $process->getErrorOutput()
            ));
        }

        return $process->getOutput();
    }
}
