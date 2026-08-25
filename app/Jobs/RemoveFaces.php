<?php

namespace App\Jobs;

use App\Models\ArticleImage;
use Google\Cloud\Vision\V1\AnnotateImageRequest;
use Google\Cloud\Vision\V1\BatchAnnotateImagesRequest;
use Google\Cloud\Vision\V1\Client\ImageAnnotatorClient;
use Google\Cloud\Vision\V1\Feature;
use Google\Cloud\Vision\V1\Feature\Type;
use Google\Cloud\Vision\V1\Image as VisionImage;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;
use Spatie\Image\Enums\AlignPosition;
use Spatie\Image\Enums\Fit;
use Spatie\Image\Image as SpatieImage;
use Throwable;

class RemoveFaces implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;
    public int $timeout = 90;

    private $article_image_id;

    public function __construct($article_image_id)
    {
        $this->article_image_id = $article_image_id;
    }

    public function handle(): void
    {
        ini_set('memory_limit', '512M');
        set_time_limit(120);

        $i = ArticleImage::find($this->article_image_id);
        if (!$i) {
            return;
        }

        $src = storage_path('app/public/' . $i->path);
        if (!file_exists($src)) {
            return;
        }

        $credentialsPath = base_path('google_credential.json');
        if (!file_exists($credentialsPath)) {
            Log::error("RemoveFaces: google_credential.json non trovato.");
            return;
        }

        try {
            $imageContent = file_get_contents($src);

            $googleVisionClient = new ImageAnnotatorClient([
                'credentials' => $credentialsPath,
                'requestTimeout' => 20.0,
            ]);

            $google_image = new VisionImage(['content' => $imageContent]);

            // 1. Chiediamo a Google sia Rilevazione Volti sia SafeSearch (Temi)
            $featureFace = new Feature();
            $featureFace->setType(Type::FACE_DETECTION);

            $featureSafe = new Feature();
            $featureSafe->setType(Type::SAFE_SEARCH_DETECTION);

            $request = new AnnotateImageRequest();
            $request->setImage($google_image);
            $request->setFeatures([$featureFace, $featureSafe]); // Inviamo entrambi in una sola chiamata!

            $batchRequest = new BatchAnnotateImagesRequest();
            $batchRequest->setRequests([$request]);

            $responseBatch = $googleVisionClient->batchAnnotateImages($batchRequest);
            $response = $responseBatch->getResponses()[0];

            if ($response->hasError()) {
                Log::error("RemoveFaces Vision Error: " . $response->getError()->getMessage());
                $googleVisionClient->close();
                return;
            }

            // --- A) PARTE SAFESEARCH (MODERAZIONE TEMI) ---
            $safeSearch = $response->getSafeSearchAnnotation();
            if ($safeSearch) {
                // Mappa dei livelli di probabilità forniti da Google Vision (numeri da 0 a 5)
                $likelihoodName = [
                    'text-secondary bi bi-circle-fill',      // UNKNOWN / VERY_UNLIKELY
                    'text-success bi bi-check-circle-fill',  // UNLIKELY
                    'text-success bi bi-check-circle-fill',  // POSSIBLE
                    'text-warning bi bi-exclamation-circle-fill', // LIKELY
                    'text-danger bi bi-dash-circle-fill'    // VERY_LIKELY
                ];

                $i->adult = $likelihoodName[$safeSearch->getAdult()] ?? $likelihoodName[0];
                $i->spoof = $likelihoodName[$safeSearch->getSpoof()] ?? $likelihoodName[0];
                $i->medical = $likelihoodName[$safeSearch->getMedical()] ?? $likelihoodName[0];
                $i->violence = $likelihoodName[$safeSearch->getViolence()] ?? $likelihoodName[0];
                $i->racy = $likelihoodName[$safeSearch->getRacy()] ?? $likelihoodName[0];
                $i->save();
            }

            // --- B) PARTE CENSURA VOLTI ---
            $faces = $response->getFaceAnnotations();

            if (count($faces) > 0) {
                $watermarkPath = base_path('resources/img/face.png');

                if (file_exists($watermarkPath)) {
                    $spatieImage = SpatieImage::load($src);
                    $origWidth = $spatieImage->getWidth();
                    $origHeight = $spatieImage->getHeight();

                    foreach ($faces as $face) {
                        $vertices = $face->getBoundingPoly()->getVertices();

                        $xCoordinates = [];
                        $yCoordinates = [];
                        foreach ($vertices as $vertex) {
                            $xCoordinates[] = $vertex->getX();
                            $yCoordinates[] = $vertex->getY();
                        }

                        $minX = max(0, min($xCoordinates));
                        $minY = max(0, min($yCoordinates));
                        $maxX = min($origWidth, max($xCoordinates));
                        $maxY = min($origHeight, max($yCoordinates));

                        $w = $maxX - $minX;
                        $h = $maxY - $minY;

                        if ($w > 0 && $h > 0) {
                            $spatieImage->watermark(
                                $watermarkPath,
                                AlignPosition::TopLeft,
                                paddingX: (int) $minX,
                                paddingY: (int) $minY,
                                width: (int) $w,
                                height: (int) $h,
                                fit: Fit::Stretch
                            );
                        }
                    }

                    $spatieImage->save($src);

                    // Censura anche watermarked_path se già presente
                    if ($i->watermarked_path) {
                        $wmSrc = storage_path('app/public/' . $i->watermarked_path);
                        if (file_exists($wmSrc)) {
                            $spatieWmImage = SpatieImage::load($wmSrc);
                            $wmWidth = $spatieWmImage->getWidth();
                            $wmHeight = $spatieWmImage->getHeight();

                            $scaleX = $origWidth > 0 ? ($wmWidth / $origWidth) : 1;
                            $scaleY = $origHeight > 0 ? ($wmHeight / $origHeight) : 1;

                            foreach ($faces as $face) {
                                $vertices = $face->getBoundingPoly()->getVertices();
                                $xCoords = [];
                                $yCoords = [];
                                foreach ($vertices as $vertex) {
                                    $xCoords[] = $vertex->getX();
                                    $yCoords[] = $vertex->getY();
                                }

                                $minX = max(0, min($xCoords));
                                $minY = max(0, min($yCoords));
                                $maxX = max($xCoords);
                                $maxY = max($yCoords);

                                $w = (int) (($maxX - $minX) * $scaleX);
                                $h = (int) (($maxY - $minY) * $scaleY);
                                $posX = (int) ($minX * $scaleX);
                                $posY = (int) ($minY * $scaleY);

                                if ($w > 0 && $h > 0) {
                                    $spatieWmImage->watermark(
                                        $watermarkPath,
                                        AlignPosition::TopLeft,
                                        paddingX: $posX,
                                        paddingY: $posY,
                                        width: $w,
                                        height: $h,
                                        fit: Fit::Stretch
                                    );
                                }
                            }
                            $spatieWmImage->save($wmSrc);
                        }
                    }
                }
            }

            $googleVisionClient->close();
        } catch (Throwable $e) {
            Log::error("RemoveFaces Exception: " . $e->getMessage());
            throw $e;
        }
    }
}
