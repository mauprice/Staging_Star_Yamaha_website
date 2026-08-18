<?php

return [
    'name'    => 'Jimboomba Star Yamaha and Honda',
    'address' => [
        'street'   => '6 Cerina Circuit',
        'suburb'   => 'Jimboomba',
        'state'    => 'QLD',
        'postcode' => '4280',
        'full'     => '6 Cerina Circuit, Jimboomba QLD 4280',
    ],
    'phone' => [
        'href'    => 'tel:+61755477759',
        'display' => '(07) 5547 7759',
    ],
    // Only one inbox is published on staryamaha.com.au — every department
    // key points to it rather than guessing at sales@/service@ addresses
    // that may not exist.
    'email' => [
        'sales'     => 'info@staryamaha.com.au',
        'service'   => 'info@staryamaha.com.au',
        'spares'    => 'info@staryamaha.com.au',
        'enquiries' => 'info@staryamaha.com.au',
    ],
    'hours' => [
        ['days' => 'Monday – Friday', 'hours' => '8:00am – 5:00pm', 'closed' => false],
        ['days' => 'Saturday',        'hours' => '8:00am – 1:00pm', 'closed' => false],
        ['days' => 'Sunday',          'hours' => 'Closed',          'closed' => true],
    ],
    // No Instagram is published on staryamaha.com.au, so there's no
    // 'instagram' key here — see the templates that used to reference it.
    'social' => [
        'facebook' => 'https://www.facebook.com/jimboombastaryamaha/',
    ],
    'map' => [
        'lat' => -27.83586578273882,
        'lng' => 153.02274081506485,
    ],
];
