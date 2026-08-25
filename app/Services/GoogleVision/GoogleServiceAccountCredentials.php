<?php

namespace App\Services\GoogleVision;

use Illuminate\Support\Facades\File;
use JsonException;
use RuntimeException;

class GoogleServiceAccountCredentials
{
    /**
     * @return array<string, mixed>
     */
    public function load(): array
    {
        $configuredPath = (string) config('services.google_vision.credentials_path');
        $credentialsPath = File::exists($configuredPath)
            ? $configuredPath
            : base_path($configuredPath);

        if ($configuredPath === '' || ! File::isFile($credentialsPath)) {
            throw new RuntimeException('Google Vision credentials are not available.');
        }

        $contents = File::get($credentialsPath);
        $credentials = $this->decodeCredentials($contents);

        foreach (['type', 'client_email', 'private_key'] as $requiredKey) {
            if (! isset($credentials[$requiredKey]) || ! is_string($credentials[$requiredKey])) {
                throw new RuntimeException("Google Vision credentials are missing [{$requiredKey}].");
            }
        }

        if ($credentials['type'] !== 'service_account') {
            throw new RuntimeException('Google Vision credentials must belong to a service account.');
        }

        return $credentials;
    }

    /**
     * @return array<string, mixed>
     */
    private function decodeCredentials(string $contents): array
    {
        try {
            $credentials = json_decode($contents, true, flags: JSON_THROW_ON_ERROR);
        } catch (JsonException) {
            $credentials = null;
        }

        if (is_array($credentials)) {
            return $credentials;
        }

        if (
            preg_match(
                '/\{\s*"type"\s*:\s*"service_account".*?\}/s',
                $contents,
                $matches,
            ) !== 1
        ) {
            throw new RuntimeException('Google Vision service-account credentials could not be decoded.');
        }

        try {
            $credentials = json_decode($matches[0], true, flags: JSON_THROW_ON_ERROR);
        } catch (JsonException $exception) {
            throw new RuntimeException(
                'Google Vision service-account credentials could not be decoded.',
                previous: $exception,
            );
        }

        if (! is_array($credentials)) {
            throw new RuntimeException('Google Vision service-account credentials are invalid.');
        }

        return $credentials;
    }
}
