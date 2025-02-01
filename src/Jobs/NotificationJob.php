<?php

namespace MhdElawi\Notification\Jobs;

use Google\Auth\Credentials\ServiceAccountCredentials;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use MhdElawi\Notification\Exceptions\MissingFirebaseConfigurationException;

class NotificationJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private const GOOGLE_ACCESS_TOKEN_CACHE_KEY = 'google_access_token';
    private array $config = [];
    private array $firebaseConfig = [];

    /**
     * Create a new job instance.
     */
    public function __construct(
        private array $tokens,
        private array $metadata,
        private array $title,
        private array $body
    )
    {
        $this->config = config('notification');
        $this->firebaseConfig = config('notification.firebase');
    }

    /**
     * Execute the job.
     * @throws \Exception
     */
    public function handle(): void
    {
        $this->validateFirebaseConfig();
        $notificationData = $this->prepareNotificationPayload();
        // Process tokens in chunks
        collect($this->tokens)
            ->chunk(100)
            ->each(fn($chunk) => $this->sendToFirebase($notificationData, $chunk->toArray()));
    }

    /**
     * Send the notification to Firebase for a chunk of tokens.
     *
     * @param array $notificationData
     * @param array $tokens
     * @return void
     * @throws \Illuminate\Http\Client\ConnectionException|\Throwable
     */
    private function sendToFirebase(array $notificationData, array $tokens): void
    {
        $projectId = $this->firebaseConfig['project_id'];
        $defaultFirebaseUrl = $this->firebaseConfig['url'];
        $firebaseUrl = str_replace(':project_id', $projectId, $defaultFirebaseUrl);
        $accessToken = $this->getGoogleAccessToken();

        // Send notifications for each token
        foreach ($tokens as $token) {
            $data = $notificationData;
            $data['message']['token'] = $token;
            $this->callApi($accessToken, $data, $firebaseUrl);
        }
    }


    /**
     * @throws \Illuminate\Http\Client\ConnectionException
     * @throws \Throwable
     */
    public function callApi($accessToken, $data, $firebaseUrl)
    {
        $response = retry(3, function () use ($firebaseUrl, $data, $accessToken) {
            return Http::withHeaders([
                'Authorization' => 'Bearer ' . $accessToken,
                'Content-Type' => 'application/json',
            ])->post($firebaseUrl, $data);
        }, 100);

        // Log the response
        info('Firebase response', [
            'data' => $data,
            'status' => $response->status(),
            'response' => $response->body(),
        ]);
    }


    /**
     * Retrieve or generate a Google access token.
     *
     * @return string
     */
    private function getGoogleAccessToken(): string
    {
        return Cache::remember(self::GOOGLE_ACCESS_TOKEN_CACHE_KEY, now()->addMinutes(30), function () {
            $credentialsPath = base_path($this->firebaseConfig['google_application_credentials']);
            $scopes = [$this->firebaseConfig['firebase_scope']];
            $credentials = new ServiceAccountCredentials($scopes, $credentialsPath);

            $token = $credentials->fetchAuthToken();
            return $token['access_token'] ?? '';
        });
    }


    /**
     * Validate that Firebase configuration is set correctly.
     *
     * @return void
     * @throws \Exception If Firebase configuration is missing or invalid.
     */
    private function validateFirebaseConfig(): void
    {
        if (!array_key_exists('project_id', $this->firebaseConfig)
            || !array_key_exists('url', $this->firebaseConfig)) {
            throw new MissingFirebaseConfigurationException();
        }

    }


    /**
     * Prepare the notification payload.
     *
     * @return array
     */
    private function prepareNotificationPayload(): array
    {
        return [
            'message' => [
                'notification' => $this->getNotificationData(),
                'data' => $this->getDataFields(),
            ],
        ];
    }

    /**
     * Retrieve the notification title and body.
     *
     * @return array
     */
    private function getNotificationData(): array
    {
        $defaultLang = $this->config['default_language'] ?? 'en';

        return [
            'title' => $this->title[$defaultLang] ?? '',
            'body' => $this->body[$defaultLang] ?? '',
        ];
    }

    /**
     * Generate the data fields for the notification.
     *
     * @return array
     */
    private function getDataFields(): array
    {
        $dataFields = $this->getLocalizedContent();
        $dataFields = $this->getExtraFields($dataFields);
        return array_merge($dataFields, $this->getRelatedEntityData());
    }

    /**
     * Retrieve the localized notification titles and bodies.
     *
     * @return array
     */
    private function getLocalizedContent(): array
    {
        $localizedContent = [];

        foreach ($this->title as $lang => $title) {
            $localizedContent["title_{$lang}"] = $title;
            $localizedContent["body_{$lang}"] = $this->body[$lang] ?? '';
        }

        return $localizedContent;
    }

    /**
     * Retrieve additional metadata fields.
     *
     * @param array $dataFields
     * @return array
     */
    private function getExtraFields(array $dataFields): array
    {
        foreach ($title->metadata['extra_fields'] ?? [] as $key => $value)
            $dataFields[$key] = $value;

        $dataFields['icon'] = $this->metadata['icon'] ?? null;

        return $dataFields ;
    }

    /**
     * Retrieve related entity information if available.
     *
     * @return array
     */
    private function getRelatedEntityData(): array
    {
        if (empty($this->metadata['related_type']) || empty($this->metadata['related_id'])) {
            return [];
        }

        return [
            'click_action' => $this->firebaseConfig['click_action'] ?? '',
            'screen' => strtolower(class_basename($this->metadata['related_type'])),
            'id' => (string) $this->metadata['related_id'],
        ];
    }

}
