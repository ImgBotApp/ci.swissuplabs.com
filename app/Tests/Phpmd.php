<?php
namespace App\Tests;

use App\Lib\Terminal;

/**
 * @see http://docs.swissuplabs.com/m1/dev/#phpmd
 * wget -c http://static.phpmd.org/php/latest/phpmd.phar
 * sudo chmod a+x phpmd.phar
 * sudo mv phpmd.phar /usr/local/bin/phpmd
 */
class Phpmd extends Test
{
    public function getTitle()
    {
        return 'PHP Mess Detector';
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
                "%s %s text %s",
                storage_path('app/tools/phpmd'),
                escapeshellarg($this->getPath()),
                storage_path('app/tools/m2/dev/tests/static/testsuite/Magento/Test/Php/_files/phpmd/ruleset.xml')
            )
        ]);
        $output = Terminal::exec($command);
        $output = str_replace(storage_path(), '', $output);
        return $output;
    }
}
