<?php

return [
    'punctuality_thresholds' => [
        'on_time' => 15, // Visits 'arrived_at' within 15 minutes of 'start'
        'late' => 30, // Visit 'arrived_at' more than 15 minutes of 'start', but less than 30 minutes of 'start'
        // 'missed' if 'arrived_at' is empty and 'start' < NOW
    ],
];
