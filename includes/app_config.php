<?php
/**
 * Centralized app-level configuration.
 * Keep this file server-side only and do not expose it publicly.
 */

return [
    'mail' => [
        'smtp_host' => 'smtp.gmail.com',
        'smtp_port' => 587,
        'smtp_secure' => 'tls',
        'smtp_auth' => true,
        'smtp_username' => 'no-reply@pluswealth.com',
        'smtp_password' => 'qrgdaodurjmhajry',
        'from_email' => 'no-reply@pluswealth.com',
        'from_name_admin' => 'PlusWealth PMS Contact Form',
        'from_name_user' => 'PlusWealth Capital Management',
        'to_emails' => [
            'amit.mishra@pluswealth.net',
            'pavit.singh@pluswealth.com',
        ],
    ],
    'leads_portal' => [
        // Separate password for leads-only page (not CMS/admin password)
        'password' => 'LeadsTeam2026!',
    ],
];
