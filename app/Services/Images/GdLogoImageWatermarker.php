<?php

namespace App\Services\Images;

use App\Contracts\ImageWatermarker;
use Illuminate\Support\Facades\File;
use RuntimeException;

class GdLogoImageWatermarker implements ImageWatermarker
{
    public function apply(string $imageContents): string
    {
        if (! extension_loaded('gd') || ! function_exists('imagewebp')) {
            throw new RuntimeException('The GD extension with WebP support is required.');
        }

        $logoPath = (string) config('images.watermark.logo_path');

        if (! File::isFile($logoPath)) {
            throw new RuntimeException("The watermark logo [{$logoPath}] does not exist.");
        }

        $sourceImage = imagecreatefromstring($imageContents);
        $logoImage = imagecreatefromstring(File::get($logoPath));

        if ($sourceImage === false || $logoImage === false) {
            if ($sourceImage instanceof \GdImage) {
                imagedestroy($sourceImage);
            }

            if ($logoImage instanceof \GdImage) {
                imagedestroy($logoImage);
            }

            throw new RuntimeException('The source image or watermark logo could not be decoded.');
        }

        try {
            $watermark = $this->resizeLogo($logoImage, $sourceImage);

            try {
                $this->placeWatermark($sourceImage, $watermark);

                return $this->encode($sourceImage);
            } finally {
                imagedestroy($watermark);
            }
        } finally {
            imagedestroy($logoImage);
            imagedestroy($sourceImage);
        }
    }

    private function resizeLogo(\GdImage $logoImage, \GdImage $sourceImage): \GdImage
    {
        $sourceShortestSide = min(imagesx($sourceImage), imagesy($sourceImage));
        $relativeSize = (float) config('images.watermark.relative_size', 0.18);
        $maximumSize = (int) config('images.watermark.maximum_size', 240);
        $watermarkWidth = max(1, min($maximumSize, (int) round($sourceShortestSide * $relativeSize)));
        $watermarkHeight = max(1, (int) round(
            $watermarkWidth * imagesy($logoImage) / imagesx($logoImage),
        ));
        $watermark = imagecreatetruecolor($watermarkWidth, $watermarkHeight);

        if ($watermark === false) {
            throw new RuntimeException('The watermark canvas could not be created.');
        }

        imagealphablending($watermark, false);
        imagesavealpha($watermark, true);
        $transparent = imagecolorallocatealpha($watermark, 0, 0, 0, 127);
        imagefill($watermark, 0, 0, $transparent);

        if (! imagecopyresampled(
            $watermark,
            $logoImage,
            0,
            0,
            0,
            0,
            $watermarkWidth,
            $watermarkHeight,
            imagesx($logoImage),
            imagesy($logoImage),
        )) {
            imagedestroy($watermark);

            throw new RuntimeException('The watermark logo could not be resized.');
        }

        return $watermark;
    }

    private function placeWatermark(\GdImage $sourceImage, \GdImage $watermark): void
    {
        $shortestSide = min(imagesx($sourceImage), imagesy($sourceImage));
        $paddingRatio = (float) config('images.watermark.padding_ratio', 0.05);
        $padding = max(1, (int) round($shortestSide * $paddingRatio));
        $destinationX = max(0, imagesx($sourceImage) - imagesx($watermark) - $padding);
        $destinationY = max(0, imagesy($sourceImage) - imagesy($watermark) - $padding);

        imagealphablending($sourceImage, true);
        imagesavealpha($sourceImage, true);

        if (! imagecopy(
            $sourceImage,
            $watermark,
            $destinationX,
            $destinationY,
            0,
            0,
            imagesx($watermark),
            imagesy($watermark),
        )) {
            throw new RuntimeException('The watermark logo could not be applied.');
        }
    }

    private function encode(\GdImage $sourceImage): string
    {
        ob_start();

        try {
            $quality = (int) config('images.watermark.quality', 88);

            if (! imagewebp($sourceImage, null, $quality)) {
                throw new RuntimeException('The watermarked image could not be encoded as WebP.');
            }

            $contents = ob_get_contents();

            if (! is_string($contents) || $contents === '') {
                throw new RuntimeException('The encoded watermarked image is empty.');
            }

            return $contents;
        } finally {
            ob_end_clean();
        }
    }
}
