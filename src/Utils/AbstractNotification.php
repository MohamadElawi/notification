<?php

namespace MhdElawi\Notification\Utils;

use Closure;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Lang;
use MhdElawi\Notification\Exceptions\InvalidLocaleException;


/**
 * AbstractNotification serves as a base class for building notifications.
 * It provides methods to set user data, titles, body content, icons, related data, and localized translations.
 *
 * Properties such as `$config`, `$languages`, and `$data` are initialized based on the application's configuration.
 */
class AbstractNotification
{
    /**
     * Configuration data for notifications.
     * Loaded from `config('notification')`.
     *
     * @var array
     */
    protected array $config = [];

    /**
     * Holds additional data for the notification (e.g., related model details).
     *
     * @var array
     */
    protected array $metadata = [];

    /**
     * A collection or array of recipients who will receive the notification.
     *
     * @var array|Collection
     */
    protected array|Collection $recipients = [];

    /**
     * Stores the notification title for each locale.
     *
     * @var array|null
     */
    protected array|null $title = [];

    /**
     * Stores the notification body content for each locale.
     *
     * @var array|null
     */
    protected array|null $body = [];

    /**
     * Icon associated with the notification.
     * Can be a string or a closure that resolves to a string.
     *
     * @var Closure|string|null
     */
    protected Closure|string|null $icon = null;

    /**
     * Color associated with the notification.
     * Can be a string or a closure that resolves to a string.
     *
     * @var Closure|string|null
     */
    protected Closure|string|null $color = null;

    /**
     * Image associated with the notification.
     * Can be a string or a closure that resolves to a string.
     *
     * @var Closure|string|null
     */
    protected Closure|string|null $image = null;

    /**
     * Sound associated with the notification.
     * Can be a string or a closure that resolves to a string.
     *
     * @var Closure|string|null
     */
    protected Closure|string|null $sound = null;

    /**
     * Tag associated with the notification.
     * Can be a string or a closure that resolves to a string.
     *
     * @var Closure|string|null
     */
    protected Closure|string|null $tag = null;

    /**
     * Supported languages for the notification.
     *
     * @var array
     */
    protected array $languages = [];

    /**
     * Initialize the AbstractNotification class.
     * Sets up configuration and supported languages.
     *
     * @param NotificationUtils $notificationUtils
     */
    public function __construct(protected NotificationUtils $notificationUtils)
    {
        $this->config = config('notification');
        $this->languages = $this->config['languages'];
    }


    /**
     * /**
     * Normalize and set user data into a collection for the notification.
     *
     * @param mixed $recipients Can be a collection, paginator, array, or single user instance.
     * @return static
     */
    public function setRecipientData(mixed $recipients): static
    {
        $recipients = $this->normalizeRecipients($recipients);

//        foreach ($recipients as $user) {
//            $this->recipients[] = [
//                'model_id' => $user->id,
//                'model_type' => get_class($user),
//            ];
//        }

        $this->recipients = $recipients;

        return $this;
    }


    /**
     * Set related model data for the notification.
     *
     * @param mixed $related The related object (must have `id` and class).
     * @return static
     */
    public function setRelatedModel(mixed $related): static
    {
        if ($related)
            $this->metadata = array_merge($this->metadata, [
                'related_id' => $related->id,
                'related_type' => get_class($related)
            ]);

        return $this;
    }

    /**
     * Set the notification title for a specific locale.
     *
     * @param string|Closure $message The title message (can be a string or closure).
     * @param string|null $locale The locale for the title (defaults to the current locale).
     * @return static
     * @throws \Exception If the locale is not supported.
     */
    public function setTitle(string|Closure $message, string $locale = null): static
    {
        $locale = $this->getLocale($locale);

        $this->title[$locale] = $this->evaluate($message);
        return $this;
    }


    /**
     * Set the notification body content for a specific locale.
     *
     * @param string|Closure $message The body message (can be a string or closure).
     * @param string|null $locale The locale for the body (defaults to the current locale).
     * @return static
     * @throws \Exception If the locale is not supported.
     */
    public function setBody(string|Closure $message, string $locale = null): static
    {
        $locale = $this->getLocale($locale);

        $this->body[$locale] = $this->evaluate($message);
        return $this;
    }

    /**
     * Set the icon for the notification.
     *
     * @param string|Closure $icon The icon (can be a string or closure).
     * @return static
     */
    public function setIcon(string|Closure $icon): static
    {
        $this->metadata['icon'] = $this->evaluate($icon);
        return $this;
    }

    /**
     * Set extra fields for the notification. like (Image, Color, Sound, Tag)
     *
     * @param array|Closure $extraFields The extra fields (can be array or closure).
     * @return static
     */
    public function setExtraFields(array|Closure $extraFields): static
    {
        $value = $this->evaluate($extraFields);
        $this->metadata['extra_fields'] = $value;
        return $this;
    }


    /**
     * Set the notification title in all supported languages using translations.
     *
     * @param string $message Translation key for the title.
     * @param array $variables Variables to replace in the translation string.
     * @return static
     */
    public function setTitleWithTranslation(string $message, array $variables = []): static
    {
        foreach ($this->languages as $lang) {
            $this->title[$lang] = Lang::get($message, $variables, $lang);
        }
        return $this;
    }

    /**
     * Set the notification body in all supported languages using translations.
     *
     * @param string $message Translation key for the body.
     * @param array $variables Variables to replace in the translation string.
     * @return static
     */
    public function setBodyWithTranslation(string $message, array $variables = []): static
    {
        foreach ($this->languages as $lang) {
            $this->body[$lang] = Lang::get($message, $variables, $lang);
        }
        return $this;
    }


    /**
     * Validate if the given locale is supported.
     *
     * @param string $locale The locale to check.
     * @return void
     * @throws \Exception If the locale is not supported.
     */

    private function checkLanguageIfExistsIsExistsInConfig(string $locale): void
    {
        if (!in_array($locale, $this->languages))
            throw new InvalidLocaleException();
    }

    /**
     * Retrieve the current or specified locale, ensuring it is supported.
     *
     * @param string|null $locale The requested locale (optional).
     * @return string The validated locale.
     * @throws \Exception If the locale is not supported.
     */
    public function getLocale(string $locale = null): string
    {
        $locale = is_null($locale) ? $this->config['default_language'] : $locale;
        $this->checkLanguageIfExistsIsExistsInConfig($locale);
        return $locale;
    }

    /**
     * Evaluate the given value if it is a closure, otherwise return it as is.
     *
     * @param mixed $value The value to evaluate.
     * @return mixed The evaluated value.
     */
    private function evaluate(mixed $value): mixed
    {
        return $value instanceof Closure ? $value() : $value;
    }

    /**
     * Normalize recipients into a collection.
     * Handles paginators, collections, arrays, and single instances.
     *
     * @param mixed $recipients The input recipients.
     * @return Collection The normalized collection of recipients.
     */
    public function normalizeRecipients(mixed $recipients): Collection
    {
        return $recipients instanceof LengthAwarePaginator || $recipients instanceof Paginator
            ? $recipients->getCollection()
            : ($recipients instanceof Collection
                ? $recipients
                : collect(is_array($recipients) ? $recipients : [$recipients]));
    }

}
