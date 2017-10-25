<?php

return [

    /*
    |------------------------------------------------------------------
    | Repository names that shoudn't trigger any logic
    |------------------------------------------------------------------
    | Usefull if webhook is added for organization
    | while some repos don't have anything to validate
    | with our tests.
    */

    'ignore' => [
        'tmhub/packages',
        'swissup/packages',
        'swissup/swissup.github.io',
    ],

];
