<?php

// Single source of truth for the product taxonomy used by both the desktop
// mega-menu and the mobile accordion menu in resources/views/yamaha/layout.blade.php.
// Previously this array was hand-duplicated in two places and had drifted
// ("ATV / ROV" vs "ATV/ROV").

return [
    'groups' => [
        'road'       => ['label' => 'Road',       'categories' => ['supersport' => 'Supersport', 'maximum-torque' => 'Maximum Torque', 'sport-heritage' => 'Sport Heritage', 'sport-touring' => 'Sport Touring', 'scooter' => 'Scooter']],
        'off-road'   => ['label' => 'Off Road',   'categories' => ['motocross' => 'Motocross', 'enduro' => 'Enduro', 'fun' => 'Fun', 'adventure' => 'Adventure', 'agriculture' => 'Agriculture']],
        'atv-rov'    => ['label' => 'ATV / ROV',  'categories' => ['sport-atv' => 'Sport ATV', 'fun-atv' => 'Fun ATV', 'sport-rov' => 'Sport ROV', 'utility-rov' => 'Utility ROV']],
        'golf-car'   => ['label' => 'Golf Car',   'categories' => ['electric' => 'Electric', 'petrol' => 'Petrol', 'utility' => 'Utility']],
        'watercraft' => ['label' => 'Watercraft', 'categories' => ['recreational' => 'Recreational', 'luxury-performance' => 'Luxury Performance', 'race-inspired' => 'Race Inspired', 'freestyle' => 'Freestyle']],
    ],
];
