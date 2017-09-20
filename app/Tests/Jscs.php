<?php
namespace App\Tests;

use App\Lib\Terminal;

/**
 * @see http://docs.swissuplabs.com/m1/dev/#jscs
 */
class Jscs extends Test
{
    public function getTitle()
    {
        return 'JSCS is a code style linter and formatter for your style guide';
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
                "jscs %s --config %s",
                /*escapeshellarg(*/$this->getPath() . '/view/**'/*)*/,
                storage_path('app/tools/m2/dev/tests/static/testsuite/Magento/Test/Js/_files/jscs/.jscsrc')
            )
        ]);
        return Terminal::exec($command);
    }
}
