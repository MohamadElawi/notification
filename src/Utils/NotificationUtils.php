<?php

namespace MhdElawi\Notification\Utils;


use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Bus;
use MhdElawi\Notification\Contracts\HasNotification;
use MhdElawi\Notification\Exceptions\InvalidRecipientException;
use MhdElawi\Notification\Jobs\NotificationJob;
use MhdElawi\Notification\Models\Notification;
use MhdElawi\Notification\Traits\NotificationTranslationTrait;

/**
 * NotificationUtils handles the core utility functions for notifications,
 * such as retrieving device tokens, dispatching notifications, and saving notifications
 * with translations.
 */
class NotificationUtils
{
    use NotificationTranslationTrait;


    /**
     * Retrieve unique device tokens for the given recipients.
     *
     * @param mixed $recipients A collection of recipients implementing the HasNotification interface.
     * @return array The unique list of device tokens.
     * @throws \Exception If a user does not implement the HasNotification interface.
     */
    public function getDeviceTokens(mixed $recipients): array
    {
        return $recipients
            ->map(function ($recipient) {
                return $recipient instanceof HasNotification ? $recipient->getDeviceTokens()
                    : throw new InvalidRecipientException();
            })
            ->flatten()
            ->unique()
            ->values()
            ->toArray();
    }


    /**
     * Dispatch a notification to the provided device tokens in chunks.
     *
     * @param array $tokens An array of device tokens.
     * @param array $metaData Metadata for the notification.
     * @param array $title The notification title, translated for each locale.
     * @param array $body The notification body, translated for each locale.
     * @return void
     */
    public function dispatchNotification(array $tokens, array $metaData, array $title, array $body): void
    {
        collect(array_chunk($tokens, 1000))
            ->each(fn($tokenChunk) => dispatch(new NotificationJob($tokenChunk, $metaData, $title, $body))
                ->onQueue('notifications'));
    }


//    public function dispatchNotification(array $tokens, array $metaData, array $title, array $body)
//    {
//        $jobs = collect(array_chunk($tokens, 1000))
//            ->map(fn($tokenChunk) => new NotificationJob($tokenChunk, $metaData, $title, $body));
//
//        Bus::batch($jobs)->dispatch();  // Use batch dispatch for more efficient handling
//    }


    /**
     * Save a notification for each user and persist its translations.
     *
     * @param mixed $recipients A collection or array of user objects.
     * @param array $metaData Metadata for the notification.
     * @param array $title The notification title, translated for each locale.
     * @param array $body The notification body, translated for each locale.
     * @return void
     */
    public function saveNotification(mixed $recipients, array $metaData, array $title, array $body)
    {
        foreach ($recipients as $recipient) {

            $metaData['model_id'] = $recipient->id;
            $metaData['model_type'] = get_class($recipient);
            $notification = Notification::query()->create($metaData);

            $this->saveTranslation($notification, $title, $body);
        }
    }


}
