<?php

namespace MhdElawi\Notification\Utils;


use Illuminate\Support\Collection;
use MhdElawi\Notification\Traits\NotificationTranslationTrait;

/**
 * Notification class is responsible for managing and sending notifications.
 * It extends AbstractNotification to leverage shared functionality for handling
 * recipients, metadata, translations, and configurations.
 */
class Notification extends AbstractNotification
{
    use NotificationTranslationTrait;

    /**
     * Flag to indicate whether the notification should be saved to the database.
     *
     * @var bool
     */
    protected bool $saveNotification = true;


    /**
     * Flag to indicate whether the notification should be sent.
     *
     * @var bool
     */
    protected bool $sendNotification = true;


    /**
     * Initialize the Notification with recipients and a related model.
     *
     * @param mixed $recipients A collection, paginator, array, or single user instance.
     * @param mixed|null $relatedModel The related model for the notification (optional).
     */
    private function __construct(mixed $recipients = [], mixed $relatedModel = null)
    {
        parent::__construct(app(NotificationUtils::class));
        $this->setRecipientData($recipients);
        $this->setRelatedModel($relatedModel);
    }


    /**
     * Static factory method to initialize Notification with recipients and a related model.
     *
     * @param mixed $recipients A collection, paginator, array, or single user instance.
     * @param mixed|null $relatedModel The related model for the notification (optional).
     * @return self
     */
    public static function for(mixed $recipients, mixed $relatedModel = null): self
    {
        return new self($recipients, $relatedModel);
    }


    /**
     * Send a notification to recipients with the specified metadata, title, and body.
     *
     * @param bool $saveNotification Flag to indicate whether the notification should be saved to the database.
     * @return void
     * @throws \Exception
     */
    public function send(bool $saveNotification = true)
    {
        $this->saveNotification = $saveNotification;

        if ($this->checkSaveNotificationIsEnabled())
            $this->saveNotificationToDatabase();


        if ($this->checkSendNotificationIsEnabled())
            $this->dispatchNotification();
    }


    /**
     * Disables sending notifications for the current instance.
     *
     * @return self Returns the current instance for method chaining.
     */
    public function disableSendNotification(): self
    {
        $this->sendNotification = false;
        return $this;
    }

    /**
     *
     */
    private function saveNotificationToDatabase(): void
    {
        $this->notificationUtils->saveNotification($this->recipients, $this->metadata, $this->title, $this->body);
    }

    /**
     * @throws \Exception
     */
    private function dispatchNotification(): void
    {
        $tokens = $this->notificationUtils->getDeviceTokens($this->recipients);

        if (!empty($tokens)) {
            $this->notificationUtils->dispatchNotification($tokens, $this->metadata, $this->title, $this->body);
        }
    }

    /**
     * Check whether saving notifications to the database is enabled.
     *
     * @return bool
     */
    public function checkSaveNotificationIsEnabled(): bool
    {
        return $this->saveNotification && $this->config['save_notifications'];
    }

    /**
     * Check whether sending notifications is enabled in the configuration.
     *
     * @return bool
     */
    public function checkSendNotificationIsEnabled(): bool
    {
        return $this->sendNotification && $this->config['send_notifications'] == true;
    }
}
