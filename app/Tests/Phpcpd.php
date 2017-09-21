<?php
namespace App\Tests;

use App\Lib\Terminal;

/**
 * @see http://docs.swissuplabs.com/m1/dev/#phpcpd
 *
 * $ wget https://phar.phpunit.de/phpcpd.phar
 * $ chmod +x phpcpd.phar
 * $ mv phpcpd.phar /usr/local/bin/phpcpd
 */
class Phpcpd extends Test
{
    public function getTitle()
    {
        return 'Copy/Paste Detector for PHP code';
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
                // "phpcpd --fuzzy -vvv --min-lines=3 --min-tokens=30 %s",
                "phpcpd --fuzzy -vvv %s",
                escapeshellarg($this->getPath())
            )
        ]);
        $output = Terminal::exec($command);
        $fingerprint = '0.00% duplicated lines out of';
        return false !== strstr($output, $fingerprint) ? '' : $output;
    }
}