<?php
namespace App\Tests;

use App\Lib\Terminal;

class Composer extends Test
{
    public function getTitle()
    {
        return 'composer validate';
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
        return Terminal::exec($command);
    }
}
