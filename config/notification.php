<?php

return [

    // Database table names used by the notifications system
    'table_names' => [

        // Table for storing notifications
        'notifications' => 'notifications',

        // Table for storing notification translations
        'notification_translations' => 'notification_translations',
    ],

    // Column naming conventions for the notifications system
    'column_names' => [

        /*
         * Foreign key column used in the translations table
         * to link back to the notifications table.
         */
        'translation_foreign_key' => 'notification_id',

        /*
         * Column used for polymorphic relationships.
         * This is the ID of the related model (e.g., user, admin ,employee ...).
         * Rename this to 'model_uuid' if you use UUIDs as primary keys.
         */
        'model_morph_key' => 'model_id',
    ],

    // Default language for notifications
    'default_language' => 'en',

    // List of supported languages for notifications
    'languages' => ['en', 'ar', 'de', 'fr'],

    // Enable or disable saving notifications to the database
    'save_notifications' => true,


    // Enable or disable sending notifications globally
    'send_notifications' => false,


    // Firebase-related configuration settings
    'firebase' => [

        // Path to the Google service account credentials file
        // Like this app/keys/7c91255e65.json
        'google_application_credentials' =>  env('GOOGLE_APPLICATION_CREDENTIALS'),

        // Required scope for Firebase Cloud Messaging
        'firebase_scope' => 'https://www.googleapis.com/auth/firebase.messaging',

        // Firebase URL template for sending messages
        // Replace ':project_id' dynamically with your Firebase project ID
        'url' => 'https://fcm.googleapis.com/v1/projects/:project_id/messages:send',

        // Firebase project ID (can be set via environment variable)
        'project_id' => env('FIREBASE_PROJECT_ID'),

        // Click action used by the notification to trigger specific behaviors in your app
        'click_action' => 'FLUTTER_NOTIFICATION_CLICK',
    ],
];
