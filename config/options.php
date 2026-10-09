<?php

// Short fixed lists. Validate with Rule::in(config('options.xxx')) so nobody can type anything else.
return [
    'genders'           => ['Male', 'Female', 'Other'],
    'blood_groups'      => ['A+', 'A-', 'B+', 'B-', 'AB+', 'AB-', 'O+', 'O-'],
    'employment_status' => ['Employed', 'Self-employed', 'Business owner', 'Student', 'Retired', 'Unemployed'],
    'work_modes'        => ['On-site', 'Hybrid', 'Remote'],
    'preferred_contact' => ['phone', 'whatsapp'],
    'social_platforms'  => ['instagram', 'facebook', 'youtube', 'x', 'linkedin'],
    'address_types'     => ['permanent', 'present'],
];
