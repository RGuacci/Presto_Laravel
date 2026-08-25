<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ArticleImage extends Model
{
    use HasFactory;

    public const PROCESSING_PENDING = 'pending';

    public const PROCESSING_READY = 'ready';

    public const PROCESSING_FAILED = 'failed';

    public const VISION_PENDING = 'pending';

    public const VISION_PROCESSING = 'processing';

    public const VISION_READY = 'ready';

    public const VISION_FAILED = 'failed';

    public const WATERMARK_PENDING = 'pending';

    public const WATERMARK_PROCESSING = 'processing';

    public const WATERMARK_READY = 'ready';

    public const WATERMARK_FAILED = 'failed';

    protected $fillable = [
        'article_id',
        'path',
        'watermarked_path',
        'watermark_status',
        'watermarked_at',
        'card_path',
        'processing_status',
        'vision_labels',
        'vision_status',
        'vision_analyzed_at',
        'vision_safe_search',
        'vision_safe_search_status',
        'vision_safe_search_analyzed_at',
        'position',
    ];

    protected $attributes = [
        'processing_status' => self::PROCESSING_PENDING,
        'watermark_status' => self::WATERMARK_PENDING,
        'vision_status' => self::VISION_PENDING,
        'vision_safe_search_status' => self::VISION_PENDING,
    ];

    public function article(): BelongsTo
    {
        return $this->belongsTo(Article::class);
    }

    public function displayPath(): string
    {
        return $this->watermarked_path ?: $this->path;
    }

    public function safeSearchRequiresReview(): bool
    {
        foreach (['adult', 'violence', 'racy'] as $category) {
            if (in_array($this->vision_safe_search[$category] ?? null, ['likely', 'very_likely'], true)) {
                return true;
            }
        }

        return false;
    }

    protected function casts(): array
    {
        return [
            'watermarked_at' => 'datetime',
            'vision_labels' => 'array',
            'vision_analyzed_at' => 'datetime',
            'vision_safe_search' => 'array',
            'vision_safe_search_analyzed_at' => 'datetime',
        ];
    }
}
