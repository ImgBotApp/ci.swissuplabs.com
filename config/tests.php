<?php

return [

    'default' => [
        // App\Tests\ComposerJson::class
    ],

    'magento2-module' => [
        App\Tests\Phplint::class,
        App\Tests\Composer::class,
        App\Tests\Meqp::class,
        // App\Tests\Eslint::class,
        // App\Tests\Jscs::class,
    ],

    'magento-module' => [
        App\Tests\Phplint::class,
        App\Tests\Composer::class,
        App\Tests\Meqp::class
    ],

];
