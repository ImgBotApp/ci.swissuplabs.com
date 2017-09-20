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
        return 'ESLint js code style validation';
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
                "eslint --config %s --ext .js %s",
                storage_path('app/tools/m2/dev/tests/static/testsuite/Magento/Test/Js/_files/eslint/.eslintrc-magento'),
                /*escapeshellarg(*/$this->getPath() . '/view/**'/*)*/
            )
        ]);
        // dd($command);
        return Terminal::exec($command);
    }
}
