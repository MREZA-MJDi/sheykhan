<?php

return [
    // Every enabled section maps to a route and backend already present in Sheykhan.
    'features' => [
        'dashboard' => true,
        'courses' => true,
        'live' => true,
        'assignments' => true,
        'exams' => true,
        'resources' => true,
        'library' => true,
        'shop' => true,
        'messages' => true,
        'results' => true,
        'growth' => true,
        'attendance' => true,
        'achievements' => true,
        'notes' => true,
        'profile' => true,
    ],
    // Sheykhan uses its internal Laravel assessment engine, not the Quiz24 flow from the reference ZIP.
    'quiz24_notice' => false,
];
