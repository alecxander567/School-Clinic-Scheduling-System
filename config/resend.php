<?php
require_once __DIR__ . '/env.php';

// Load environment variables
Env::load(__DIR__ . '/../.env');

return [
    'api_key' => Env::get('RESEND_API_KEY', ''),
    'from_email' => Env::get('RESEND_FROM_EMAIL', 'onboarding@resend.dev'),
    'from_name' => Env::get('RESEND_FROM_NAME', 'School Clinic System')
];
