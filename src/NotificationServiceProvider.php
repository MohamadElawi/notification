<?php

namespace MhdElawi\Notification;

use Illuminate\Support\ServiceProvider;

class NotificationServiceProvider extends ServiceProvider
{
    public function register()
    {

    }

    public function boot()
    {
        // Publish config file
        $this->publishes([
            __DIR__ . '/../config/notification.php' => config_path('notification.php'),
        ], 'config');

        // Publish migrations
        $this->publishes([
            __DIR__ . '/../database/migrations' => database_path('migrations'),
        ], 'migration');

    }
}
