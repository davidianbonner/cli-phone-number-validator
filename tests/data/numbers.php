<?php

return [
    'numbers' => [
        '07712345678',
        '07712341234',
        '07283 123 32', // not valid
        '+44 07712 123 678',
        '+44 123 123 123', // Not mobile
        '+4472811113', // not valid
        '07781 123 456', // Guernsey
        '+34 123 123 123', // not valid
        '07509 111 222', // Jersery
    ],

    'map' => [
        'valid',
        'valid',
        'not valid',
        'valid',
        'not valid',
        'not valid',
        'valid',
        'not valid',
        'valid',
    ],
];
