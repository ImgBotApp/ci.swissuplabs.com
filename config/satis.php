<?php

return [

    /*
    |------------------------------------------------------------------
    | Magento1 packages
    |------------------------------------------------------------------
    */

    'm1' => [
        'username' => 'tmhub',
        'repository' => 'packages',
        'ref' => 'gh-pages',
        'types' => [
            'magento-module',
        ]
    ],

    /*
    |------------------------------------------------------------------
    | Magento2 packages
    |------------------------------------------------------------------
    */

    'm2' => [
        'username' => 'swissup',
        'repository' => 'packages',
        'ref' => 'gh-pages',
        'types' => [
            'magento2-module',
            'magento2-theme',
            'metapackage',
        ]
    ],

];
