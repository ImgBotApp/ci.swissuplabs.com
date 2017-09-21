<?php
namespace App\Tests;

use App\Lib\Terminal;

/**
 * @see http://docs.swissuplabs.com/m1/dev/#php-copypaste-detector-phpcpd
 */
class Phpcpd extends Test
{
    public function getTitle()
    {
        return 'Copy/Paste Detector';
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
                // "%s --fuzzy -vvv --min-lines=3 --min-tokens=30 %s",
                "%s --fuzzy -vvv %s",
                storage_path('app/tools/phpcpd'),
                escapeshellarg($this->getPath())
            )
        ]);
        $output = Terminal::exec($command);
        $fingerprint = '0.00% duplicated lines out of';
        return false !== strstr($output, $fingerprint) ? '' : $output;
    }
}
