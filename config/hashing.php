<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Default Hash Driver
    |--------------------------------------------------------------------------
    |
    | Existing passwords are bcrypt hashes with cost 10.
    |
    */

    'driver' => 'bcrypt',

    'bcrypt' => [
        'rounds' => env('BCRYPT_ROUNDS', 10),
        // Users created by some modules have an encrypted dummy password (User::getDummyPassword()),
        // checking it must fail as wrong password instead of throwing an exception.
        'verify' => false,
    ],

    'argon' => [
        'memory'  => 65536,
        'threads' => 1,
        'time'    => 4,
        'verify'  => true,
    ],

    // Do not rewrite users passwords on login.
    'rehash_on_login' => false,

];
