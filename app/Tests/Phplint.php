<?php
namespace App\Tests;

use App\Lib\Terminal;

/**
 * @see http://docs.swissuplabs.com/m1/dev/#phplint
 */
class Phplint extends Test
{
    public function getTitle()
    {
        return 'Native php linter';
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
                "find %s -type f -name '*.php' -exec php -d display_errors=1 -l {} \;",
                escapeshellarg($this->getPath())
            ),
            sprintf(
                "find %s -type f -name '*.phtml' -exec php -d display_errors=1 -l {} \;",
                escapeshellarg($this->getPath())
            )
        ]);
        $output = Terminal::exec($command);

        $output = explode("\n", $output);
        // $output = array_filter($output);
        $output = array_filter($output, function ($str) {
            $fingerprint = 'No syntax errors detected in';
            return false === strstr($str, $fingerprint);
        });

        return empty($output) ? '' : implode("\n", $output);
    }
}
