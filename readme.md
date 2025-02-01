# Notification Package

## Table of Contents

1. [Introduction](#introduction)
2. [Features](#features)
3. [Installation](#installation)
   - [Install the Package](#install-the-package)
   - [Publish Configuration and Migrations](#publish-configuration-and-migrations)
   - [Run Migrations](#run-migrations)
4. [Configuration](#configuration)
   - [Default Language](#default-language)
   - [Supported Languages](#supported-languages)
   - [Save Notifications](#save-notifications)

[comment]: <> (   - [Store Notifications]&#40;#store-notifications&#41;)
   - [Firebase Configuration](#firebase-configuration)
5. [Usage](#usage)
   - [Sending Notifications](#sending-notifications)
   -  - [Implementing HasNotification](#implementing-hasnotification)
   - [Disabling Persistence](#disabling-persistence)
   - [Advanced Configuration](#advanced-configuration)
6. [Contributing](#contributing)

---

## Introduction

The **Notification Package** is a Laravel package designed for managing multi-language notifications with support for:

- **Database persistence**: Save notifications for future reference.
- **Firebase Cloud Messaging (FCM)**: Send push notifications to mobile devices.
- **Multi-language support**: Easily translate notification content into multiple languages.

---

## Features

- Multi-language notifications
- Database persistence for auditability
- Integration with Firebase for push notifications
- Batch processing for scalable notifications
- Flexible configuration

---

## Installation

### Install the Package

Run the following command:

```bash
composer require mhd-elawi/notification
```

### Publish Configuration and Migrations

#### Publish Configuration File

```bash
php artisan vendor:publish --tag=config
```

*Publishes the configuration file to **`config/notification.php`**.*

#### Publish Migrations

```bash
php artisan vendor:publish --tag=migration
```

*Publishes the migration files to **`database/migrations`**.*

### Run Migrations

Run the migrations:

```bash
php artisan migrate
```

---

## Configuration

### Default Language

Set the default language for notifications:

```php
'default_language' => 'en',
```

If a language is not specified for a notification, it will fall back to this language.

### Supported Languages

Define the list of supported languages:

```php
'languages' => ['en', 'ar', 'de', 'fr'],
```

### Save Notifications

By default, notifications are saved to the database:

```php
'save_notifications' => true, // Set to false to disable
```

### Firebase Configuration

Firebase configuration is optional. If using Firebase, configure the following:

#### Firebase Credentials

Set the path to the service account credentials in the `.env` file:

```bash
GOOGLE_APPLICATION_CREDENTIALS=storage/app/firebase-credentials.json
```

#### Firebase Project ID

Set the Firebase Project ID in the `.env` file:

```bash
FIREBASE_PROJECT_ID=your-project-id
```

#### Enable FCM

Enable Firebase notifications in the configuration file:

```php
'send_notifications_via_firebase' => true,
```

---

## Usage

### Sending Notifications

#### Single User Notification

```php
use MhdElawi\Notification\Services\Notification;

Notification::for($user)
    ->setTitle('Hello, World!')
    ->setBody('This is a test notification.')
    ->sendNotification();
```

#### Multi-Language Notification

```php
Notification::for($user)
    ->setTitle('Bonjour, Monde!', 'fr')
    ->setBody('Ceci est une notification de test.', 'fr')
    ->sendNotification();
```

#### Multi-Language Notifications :
```php
    Notification::for($user)
    ->setTitleWithTranslation('notification.title', ['name' => $user->name])
    ->setBodyWithTranslation('notification.body', ['name' => $user->name])
    ->sendNotification();
```

#### Batch Notification

```php
Notification::for($users)
    ->setTitle('Batch Notification')
    ->setBody('This notification is sent to multiple users.')
    ->sendNotification();
```

#### Notification with Related Data

```php
$relatedModel = Post::query()->first();

Notification::for($user, $relatedModel)
    ->setTitle('New Comment on Your Post')
    ->setBody('Someone has commented on your post.')
    ->setIcon('comment-icon')
    ->sendNotification();
```

Alternatively, you can use setRelatedModel() to assign the related model separately:
```php
$relatedModel = Post::query()->first();

Notification::for($user)
->setRelatedModel($relatedModel) // Set the related model explicitly
->setTitle('New Comment on Your Post')
->setBody('Someone has commented on your post.')
->setIcon('comment-icon')
->sendNotification();
```


#### Notification with Extra Fields
Set extra fields for the notification. like (Image, Color, Sound, Tag)

```php
Notification::for($user)
    ->setTitle('New Comment on Your Post')
    ->setBody('Someone has commented on your post.')
    ->setIcon('comment-icon')
    ->setExtraFields(['sound' => 'something'])
    ->sendNotification();
```

---
### Implementing HasNotification

If using Firebase ensure your Recipient model implements the `HasNotification` interface:

```php
use MhdElawi\Notification\Contracts\HasNotification;

class User extends Authenticatable implements HasNotification
{
    public function getDeviceTokens(): array
    {
        return $this->device_tokens ?? [];
    }
}
```

---
### Disabling Persistence

#### Disable in Configuration

```php
'save_notifications' => false,
```

#### Disable Per Notification

```php
Notification::for($user)
    ->setTitle('Title')
    ->setBody('Body')
    ->sendNotification(false);
```

---

### Advanced Configuration

#### Batch Processing

Set the batch size in the configuration file:

```php
'batch_size' => 1000,
```

#### Customize Table Names

Update `table_names` in the configuration file to match your database schema.

---

## Contributing

1. Fork the repository.
2. Create a new branch for your feature or bug fix.
3. Submit a pull request with a detailed description of your changes.

