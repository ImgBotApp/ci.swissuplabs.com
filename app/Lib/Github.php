<?php

namespace App\Lib;

use Github\Client;

class Github
{
    /**
     * The current globally used GitHub connection.
     *
     * @var object
     */
    protected static $client;

    /**
     * Set the globally available instance of the Github\Client connection.
     *
     * @return static
     */
    public static function client()
    {
        if (is_null(static::$client)) {
            static::$client = new Client();
            static::$client->authenticate(config('github.token'), Client::AUTH_HTTP_TOKEN);
        }

        return static::$client;
    }

    /**
     * Allows to use all methods of https://github.com/KnpLabs/php-github-api library
     * with shorter notation:
     *
     * Before:
     *     $client = new \Github\Client();
     *     $client->authenticate(config('github.token'), \Github\Client::AUTH_HTTP_TOKEN);
     *     $client->api('repo')->statuses()->create(...)
     * After:
     *     Github::api('repo')->statuses()->create(...)
     *
     * @param  [type] $method     [description]
     * @param  [type] $parameters [description]
     * @return [type]             [description]
     */
    public static function __callStatic($method, $parameters)
    {
        return static::client()->$method(...$parameters);
    }
}
