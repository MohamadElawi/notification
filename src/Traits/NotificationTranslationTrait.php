<?php

namespace MhdElawi\Notification\Traits;

use MhdElawi\Notification\Models\Notification;
use MhdElawi\Notification\Models\NotificationTranslation;

trait NotificationTranslationTrait
{

    /**
     * Save translations for a notification's title and body in all supported languages.
     *
     * @param Notification $notification The notification instance.
     * @param array $title The translated titles by locale.
     * @param array $body The translated bodies by locale.
     * @return void
     */
    private function saveTranslation(Notification $notification, array $title, array $body): void
    {
        $data = $this->formatTranslationData($notification, $title, $body);
        $this->storeTranslations($data);
    }

    /**
     * @param Notification $notification
     * @param array $title
     * @param array $body
     * @return array
     */
    private function formatTranslationData(Notification $notification, array $title, array $body): array
    {
        $data = [];
        foreach ($title as $locale => $message) {
            $data[] = [
                'notification_id' => $notification->id,
                'locale' => $locale,
                'title' => $message,
                'body' => $body[$locale] ?? '',
            ];
        }
        return $data;
    }

    /**
     * @param array $data
     */
    private function storeTranslations(array $data): void
    {
        NotificationTranslation::query()->insert($data);
    }
}
