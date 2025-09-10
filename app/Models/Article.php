<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Article extends Model
{
    protected $fillable = [
        'title',
        'content',
        'summary',
        'author_id',
        'author_name',
        'author_avatar',
        'category',
        'tags',
        'featured_image',
        'images',
        'is_published',
        'is_approved',
        'is_rejected',
        'rejection_reason',
        'published_at',
        'view_count',
        'like_count',
    ];

    protected $casts = [
        'tags' => 'array',
        'images' => 'array',
        'is_published' => 'boolean',
        'is_approved' => 'boolean',
        'is_rejected' => 'boolean',
        'published_at' => 'datetime',
    ];

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'author_id');
    }
}
