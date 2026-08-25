<?php

namespace App\Models;

use Database\Factories\RevisionActionFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RevisionAction extends Model
{
    /** @use HasFactory<RevisionActionFactory> */
    use HasFactory;

    protected $fillable = [
        'article_id',
        'revisor_id',
        'previous_status',
        'new_status',
        'undone_at',
    ];

    protected function casts(): array
    {
        return [
            'previous_status' => 'boolean',
            'new_status' => 'boolean',
            'undone_at' => 'datetime',
        ];
    }

    public function article(): BelongsTo
    {
        return $this->belongsTo(Article::class);
    }

    public function revisor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'revisor_id');
    }
}
