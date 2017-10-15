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
     * @return boolean
     */
    public function canRun()
    {
        return $this->isM1() || $this->isM2();
    }

    /**
     * @return boolean
     */
    public function isM2()
    {
        return in_array($this->getRepositoryType(), [
            'magento2-module',
            'magento2-theme',
        ]);
    }

    /**
     * @return boolean
     */
    public function isM1()
    {
        return in_array($this->getRepositoryType(), [
            'magento-module',
        ]);
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
                $this->isM2() ? 'MEQP2' : 'MEQP1'
            )
        ]);

        return Terminal::exec($command);
    }
}
