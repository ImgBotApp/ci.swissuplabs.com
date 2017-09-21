<?php

return [

    'default' => [
        App\Tests\Phplint::class,
        App\Tests\Composer::class,
        App\Tests\Phpcpd::class,
        App\Tests\Meqp::class,
    ],

    'magento2-module' => [
        // App\Tests\Eslint::class,
        // App\Tests\Jscs::class,
    ],

    'magento-module' => [
    ],

];
