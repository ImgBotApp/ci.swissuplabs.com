<?php
namespace App\Tests;

use App\Lib\Terminal;

/**
 * @see http://docs.swissuplabs.com/m1/dev/#eslint
 */
class Eslint extends Test
{
    public function getTitle()
    {
        return 'ESLint';
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
                "%s --config %s --ext .js %s",
                storage_path('app/tools/node_modules/.bin/eslint'),
                storage_path('app/tools/m2/dev/tests/static/testsuite/Magento/Test/Js/_files/eslint/.eslintrc-magento'),
                /*escapeshellarg(*/$this->getPath() . '/view/**'/*)*/
            )
        ]);
        return Terminal::exec($command);
    }
}
