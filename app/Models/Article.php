<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Laravel\Scout\Searchable;

class Article extends Model
{
    use HasFactory, Searchable;

    protected $fillable = [
        'title',
        'description',
        'price',
        'category_id',
        'user_id',
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function revisionActions(): HasMany
    {
        return $this->hasMany(RevisionAction::class);
    }

    public function images(): HasMany
    {
        return $this->hasMany(ArticleImage::class)->orderBy('position');
    }

    protected function casts(): array
    {
        return [
            'is_accepted' => 'boolean',
        ];
    }

    public function toSearchableArray(): array
    {
        return [
            'title' => $this->title,
            'description' => $this->description,
            'category_id' => $this->category_id,
        ];
    }

    public function setAccepted(?bool $value): bool
    {
        $this->is_accepted = $value;
        $this->save();

        return true;
    }

    public static function toBeRevisedCount(): int
    {
        return Article::query()->whereNull('is_accepted')->count();
    }
}
