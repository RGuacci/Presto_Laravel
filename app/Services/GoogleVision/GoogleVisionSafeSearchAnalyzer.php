<?php

namespace App\Services\GoogleVision;

use App\Contracts\ImageSafeSearchAnalyzer;
use Google\ApiCore\RetrySettings;
use Google\Auth\Credentials\ServiceAccountCredentials;
use Google\Cloud\Vision\V1\AnnotateImageRequest;
use Google\Cloud\Vision\V1\BatchAnnotateImagesRequest;
use Google\Cloud\Vision\V1\Client\ImageAnnotatorClient;
use Google\Cloud\Vision\V1\Feature;
use Google\Cloud\Vision\V1\Feature\Type;
use Google\Cloud\Vision\V1\Image;
use Google\Cloud\Vision\V1\Likelihood;
use Illuminate\Support\Str;
use RuntimeException;

class GoogleVisionSafeSearchAnalyzer implements ImageSafeSearchAnalyzer
{
    public function __construct(
        private GoogleServiceAccountCredentials $serviceAccountCredentials,
    ) {}

    /**
     * @return array{adult: string, spoof: string, medical: string, violence: string, racy: string}
     */
    public function analyze(string $imageContents): array
    {
        $credentials = new ServiceAccountCredentials(
            ImageAnnotatorClient::$serviceScopes,
            $this->serviceAccountCredentials->load(),
        );
        $client = new ImageAnnotatorClient([
            'credentials' => $credentials,
            'transport' => 'rest',
        ]);

        try {
            $annotationRequest = new AnnotateImageRequest([
                'image' => new Image(['content' => $imageContents]),
                'features' => [
                    new Feature(['type' => Type::SAFE_SEARCH_DETECTION]),
                ],
            ]);
            $response = $client->batchAnnotateImages(
                BatchAnnotateImagesRequest::build([$annotationRequest]),
                ['retrySettings' => RetrySettings::logicalTimeout(30_000)],
            );
            $annotationResponse = $response->getResponses()[0] ?? null;

            if ($annotationResponse === null) {
                throw new RuntimeException('Google Vision returned no SafeSearch analysis.');
            }

            if ($annotationResponse->hasError()) {
                throw new RuntimeException(
                    'Google Vision SafeSearch analysis failed: '.$annotationResponse->getError()->getMessage(),
                );
            }

            $safeSearch = $annotationResponse->getSafeSearchAnnotation();

            if ($safeSearch === null) {
                throw new RuntimeException('Google Vision returned no SafeSearch annotation.');
            }

            return [
                'adult' => $this->likelihoodName($safeSearch->getAdult()),
                'spoof' => $this->likelihoodName($safeSearch->getSpoof()),
                'medical' => $this->likelihoodName($safeSearch->getMedical()),
                'violence' => $this->likelihoodName($safeSearch->getViolence()),
                'racy' => $this->likelihoodName($safeSearch->getRacy()),
            ];
        } finally {
            $client->close();
        }
    }

    private function likelihoodName(int $likelihood): string
    {
        return Str::lower(Likelihood::name($likelihood));
    }
}
