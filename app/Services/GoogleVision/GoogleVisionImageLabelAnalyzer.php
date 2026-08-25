<?php

namespace App\Services\GoogleVision;

use App\Contracts\ImageLabelAnalyzer;
use Google\ApiCore\RetrySettings;
use Google\Auth\Credentials\ServiceAccountCredentials;
use Google\Cloud\Vision\V1\AnnotateImageRequest;
use Google\Cloud\Vision\V1\BatchAnnotateImagesRequest;
use Google\Cloud\Vision\V1\Client\ImageAnnotatorClient;
use Google\Cloud\Vision\V1\Feature;
use Google\Cloud\Vision\V1\Feature\Type;
use Google\Cloud\Vision\V1\Image;
use RuntimeException;

class GoogleVisionImageLabelAnalyzer implements ImageLabelAnalyzer
{
    public function __construct(
        private GoogleServiceAccountCredentials $serviceAccountCredentials,
    ) {}

    /**
     * @return list<array{description: string, score: float}>
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
            $feature = new Feature([
                'type' => Type::LABEL_DETECTION,
                'max_results' => (int) config('services.google_vision.max_labels', 5),
                'model' => 'builtin/stable',
            ]);
            $annotationRequest = new AnnotateImageRequest([
                'image' => new Image(['content' => $imageContents]),
                'features' => [$feature],
            ]);
            $response = $client->batchAnnotateImages(
                BatchAnnotateImagesRequest::build([$annotationRequest]),
                ['retrySettings' => RetrySettings::logicalTimeout(30_000)],
            );
            $annotationResponse = $response->getResponses()[0] ?? null;

            if ($annotationResponse === null) {
                throw new RuntimeException('Google Vision returned no image analysis.');
            }

            if ($annotationResponse->hasError()) {
                throw new RuntimeException(
                    'Google Vision analysis failed: '.$annotationResponse->getError()->getMessage(),
                );
            }

            $minimumScore = (float) config('services.google_vision.minimum_score', 0.60);
            $labels = [];

            foreach ($annotationResponse->getLabelAnnotations() as $label) {
                if ($label->getScore() < $minimumScore) {
                    continue;
                }

                $labels[] = [
                    'description' => $label->getDescription(),
                    'score' => round($label->getScore(), 4),
                ];
            }

            return $labels;
        } finally {
            $client->close();
        }
    }
}
