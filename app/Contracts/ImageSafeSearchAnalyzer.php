<?php

namespace App\Contracts;

interface ImageSafeSearchAnalyzer
{
    /**
     * @return array{adult: string, spoof: string, medical: string, violence: string, racy: string}
     */
    public function analyze(string $imageContents): array;
}
