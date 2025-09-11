<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class News extends Model
{
    protected $table = 'news';

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
        'is_published' => 'boolean',
        'is_approved' => 'boolean',
        'is_rejected' => 'boolean',
        'published_at' => 'datetime',
        'view_count' => 'integer',
        'like_count' => 'integer',
    ];

    /**
     * Get the author that owns the news.
     */
    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'author_id');
    }

    /**
     * Get the status badge class
     */
    public function getStatusBadgeClass(): string
    {
        if ($this->is_rejected) {
            return 'bg-danger';
        } elseif ($this->is_approved && $this->is_published) {
            return 'bg-success';
        } elseif ($this->is_approved) {
            return 'bg-warning';
        } else {
            return 'bg-secondary';
        }
    }

    /**
     * Get the status text
     */
    public function getStatusText(): string
    {
        if ($this->is_rejected) {
            return 'Từ chối';
        } elseif ($this->is_approved && $this->is_published) {
            return 'Đã xuất bản';
        } elseif ($this->is_approved) {
            return 'Đã duyệt';
        } else {
            return 'Chờ duyệt';
        }
    }
}