<?php

namespace App\Contracts;

interface ImageLabelAnalyzer
{
    /**
     * @return list<array{description: string, score: float}>
     */
    public function analyze(string $imageContents): array;
}
