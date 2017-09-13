<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Github Secret to use in webhooks
    |--------------------------------------------------------------------------
    */

    'secret' => env('GITHUB_SECRET'),

    /*
    |--------------------------------------------------------------------------
    | Github personal access token
    |--------------------------------------------------------------------------
    | This token is used to pull the repo,
    | create a status for the commit and other API features.
    */

    'token' => env('GITHUB_TOKEN'),

];
