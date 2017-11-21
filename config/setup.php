<?php

return [

    'config' => [
        'github.secret' => [
            'env_key' => 'GITHUB_SECRET',
            'prompt'  => 'Enter webhook secret key'
        ],
        'github.token' => [
            'env_key' => 'GITHUB_TOKEN',
            'prompt'  => 'Enter github token',
            'postinstall' => [
                'composer:config'
            ]
        ],
    ],

    'tools' => [

        'github' => [

            /*
            |------------------------------------------------------------------
            | Magento extension quality program
            |------------------------------------------------------------------
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
            |------------------------------------------------------------------
            | Magento2
            |------------------------------------------------------------------
            | Required by meqp to run Dynamic Sniffs.
            */

            'm2' => [
                'active' => true,
                'username' => 'magento',
                'repository' => 'magento2',
                'ref' => '2.2.1',
                'postinstall' => [
                    sprintf(
                        "%s/vendor/bin/phpcs --config-set m2-path %s",
                        storage_path('app/tools/meqp'),
                        storage_path('app/tools/m2')
                    ),
                    sprintf(
                        "cd %s && composer install --no-dev --ignore-platform-reqs && bin/magento module:enable --all", // && bin/magento setup:di:compile
                        storage_path('app/tools/m2')
                    ),
                ],
            ],

            /*
            |------------------------------------------------------------------
            | Satis
            |------------------------------------------------------------------
            | Required by UpdateComposerPackages Job
            */

            'satis' => [
                'active' => true,
                'username' => 'composer',
                'repository' => 'satis',
                'ref' => 'master',
                'postinstall' => [
                    sprintf(
                        "cd %s && composer install",
                        storage_path('app/tools/satis')
                    ),
                    'chmod +x ' . storage_path('app/tools/satis/bin/satis')
                ],
            ],

        ],

        'npm' => [

            /*
            |------------------------------------------------------------------
            | ESLint
            |------------------------------------------------------------------
            */

            'eslint' => [
                'active' => true,
                'package' => 'eslint'
            ],

            /*
            |------------------------------------------------------------------
            | JSCS
            |------------------------------------------------------------------
            */

            'jscs' => [
                'active' => true,
                'package' => 'jscs'
            ],

        ],

        'url' => [

            /*
            |------------------------------------------------------------------
            | Php copy/paste detector
            |------------------------------------------------------------------
            */

            'phpcpd' => [
                'active' => true,
                'url' => 'https://phar.phpunit.de/phpcpd.phar',
                'postinstall' => [
                    'chmod +x ' . storage_path('app/tools/phpcpd')
                ]
            ],

            /*
            |------------------------------------------------------------------
            | Php mess detector
            |------------------------------------------------------------------
            */

            'phpmd' => [
                'active' => true,
                'url' => 'http://static.phpmd.org/php/latest/phpmd.phar',
                'postinstall' => [
                    'chmod +x ' . storage_path('app/tools/phpmd')
                ]
            ],

            /*
            |------------------------------------------------------------------
            | Php unit
            |------------------------------------------------------------------
            */

            'phpunit' => [
                'active' => true,
                'url' => 'https://phar.phpunit.de/phpunit-5.7.phar',
                'postinstall' => [
                    'chmod +x ' . storage_path('app/tools/phpunit')
                ]
            ],

        ],

    ],

];
