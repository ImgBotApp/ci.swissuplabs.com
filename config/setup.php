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

        /*
        |--------------------------------------------------------------------------
        | Php unit test
        |--------------------------------------------------------------------------
        */
        'phpunit' => [
            'active' => true,
            'bin' => 'tools/phpunit',
            'postinstall' => [
                'wget https://phar.phpunit.de/phpunit-6.2.phar',
                'chmod +x phpunit-6.2.phar',
                'mv phpunit-6.2.phar '  . storage_path('app/tools/phpunit')
            ]
        ],

        /*
        |--------------------------------------------------------------------------
        | Php copy/paste detector
        |--------------------------------------------------------------------------
        */
        'phpcpd' => [
            'active' => true,
            'bin' => 'tools/phpcpd',
            'postinstall' => [
                'wget https://phar.phpunit.de/phpcpd.phar',
                'chmod +x phpcpd.phar',
                'mv phpcpd.phar '  . storage_path('app/tools/phpcpd')
            ]
        ],

        /*
        |--------------------------------------------------------------------------
        | Php mess detector
        |--------------------------------------------------------------------------
        */
        'phpmd' => [
            'active' => false,
            'bin' => 'tools/phpmd',
            'postinstall' => [
                'wget -c http://static.phpmd.org/php/latest/phpmd.phar',
                'chmod +x phpmd.phar',
                'mv phpmd.phar '  . storage_path('app/tools/phpmd')
            ]
        ],

        /*
        |--------------------------------------------------------------------------
        | ESLint
        |--------------------------------------------------------------------------
        */
        'eslint' => [
            'active' => false,
            'bin' => 'tools/node_modules/.bin/eslint',
            'postinstall' => [
                'npm install --prefix ' . storage_path('app/tools') . ' eslint',
            ]
        ],

        /*
        |--------------------------------------------------------------------------
        | JSCS
        |--------------------------------------------------------------------------
        */
        'jscs' => [
            'active' => false,
            'bin' => 'tools/node_modules/.bin/jscs',
            'postinstall' => [
                'npm install --prefix ' . storage_path('app/tools') . ' jscs',
            ]
        ],
    ]

];
