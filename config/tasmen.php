<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Tasmen Application Specific Configuration
    |--------------------------------------------------------------------------
    |
    | This file is for storing the configuration for the Tasmen application.
    | This is a good place to store values that are configurable but not
    | sensitive enough to be in the .env file.
    |
    */

    'file_uploads' => [
        // Rules for task attachments
        'tasks' => [
            'rules' => 'file|mimes:pdf,jpg,jpeg,png,doc,docx,xls,xlsx|max:2048',
        ],
        // Rules for leave request attachments
        'leaves' => [
            'rules' => 'file|mimes:pdf,jpg,jpeg,png|max:2048',
        ],
    ],

    // Number of weekdays for a loan request to be due.
    'loan_request_due_days' => 3,

    'pagination' => [
        // Number of items per page for the loan request history.
        'loan_requests' => 5,
    ],

    'workload' => [
        'standard_hours' => 37.5,
        'threshold_normal' => 0.75,
        'threshold_warning' => 1.0,
    ],
];
