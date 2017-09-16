<?php

return [

    'config' => [
        'github.secret' => [
            'env_key' => 'GITHUB_SECRET',
            'prompt'  => 'Enter webhook secret key'
        ],
        'github.token' => [
            'env_key' => 'GITHUB_TOKEN',
            'prompt'  => 'Enter github token'
        ],
    ],

    'tools' => [

        /*
        |--------------------------------------------------------------------------
        | Magento extension quality program
        |--------------------------------------------------------------------------
        | Each magento extension must pass this test with --severity=10
        */

        'meqp' => [
            'active' => true,
            'username' => 'magento',
            'repository' => 'marketplace-eqp',
            'ref' => 'master',
            'postinstall' => [
                sprintf(
                    "cd %s && composer install",
                    storage_path('app/tools/meqp')
                )
            ],
        ],

        /*
        |--------------------------------------------------------------------------
        | Magento2
        |--------------------------------------------------------------------------
        | Required by meqp to run Dynamic Sniffs.
        */

        'm2' => [
            'active' => true,
            'username' => 'magento',
            'repository' => 'magento2',
            'ref' => '2.1.9',
            'postinstall' => [
                sprintf(
                    "%s --config-set m2-path %s",
                    storage_path('app/tools/meqp/vendor/bin/phpcs'),
                    storage_path('app/tools/m2')
                ),
                sprintf(
                    "cd %s && composer install --no-dev --ignore-platform-reqs && bin/magento module:enable --all", // && bin/magento setup:di:compile
                    storage_path('app/tools/m2')
                ),
            ],
        ],

        /*
        |--------------------------------------------------------------------------
        | Magento1
        |--------------------------------------------------------------------------
        */

        'm1' => [
            'active' => false,
            'username' => 'speedupmate',
            'repository' => 'Magento-CE-Mirror',
            'ref' => 'magento-ce-1.9.3.4',
        ],

    ]

];
