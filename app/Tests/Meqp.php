<?php

namespace App\Tests;

use App\Lib\Terminal;

class Meqp extends Test
{
    public function getTitle()
    {
        return 'Marketplace EQP';
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
                "%s/vendor/bin/phpcs %s --standard=%s --severity=10",
                storage_path('app/tools/meqp'),
                escapeshellarg($this->getPath()),
                $this->getRepositoryType() === 'magento2-module' ? 'MEQP2' : 'MEQP1'
            )
        ]);

        return Terminal::exec($command);
    }
}
