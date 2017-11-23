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
     * @return boolean
     */
    public function canRun()
    {
        return true;
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
                "%s --regexps-exclude '~Setup~,~Test~,~lib~,~sql~' --fuzzy -vvv %s",
                storage_path('app/tools/phpcpd'),
                escapeshellarg($this->getPath())
            )
        ]);
        $output = Terminal::exec($command);
        $fingerprints = array(
            '0.00% duplicated lines out of',
            'No files found to scan'
        );
        foreach ($fingerprints as $fingerprint) {
            if (false !== strstr($output, $fingerprint)) {
                $output = '';
            }
        }
        return $output;
    }
}
