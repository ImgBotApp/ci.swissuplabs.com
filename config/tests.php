<?php

return [

    'default' => [
        // App\Tests\ComposerJson::class
    ],

    'magento2-module' => [
        App\Tests\Meqp::class,
        // App\Tests\Eslint::class,
        // App\Tests\Jscs::class,
        App\Tests\Phplint::class
    ],

    'magento-module' => [
        App\Tests\Meqp::class,
        App\Tests\Phplint::class
    ],

];
