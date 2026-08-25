<?php

namespace App\Contracts;

interface ImageWatermarker
{
    public function apply(string $imageContents): string;
}
