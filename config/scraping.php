<?php

return [
    'admin_api_key' => env('ADMIN_API_KEY'),
    'stale_job_timeout' => env('STALE_JOB_TIMEOUT', 45),
    'indeed_email_template' => env('INDEED_EMAIL_TEMPLATE', 'hr.entry.office+{serial_number}@example.com'),
];
