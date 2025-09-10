<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Review extends Model
{
    protected $table = 'rating';

    protected $fillable = [
        'product_id',
        'user_id',
        'rating',
        'comment',
        'status',
        'moderation_note',
        'moderation_reason',
        'moderated_by',
        'moderated_at',
        'is_flagged',
        'flag_reason',
        'flag_note',
        'flagged_by',
        'flagged_at',
        'flag_count',
    ];

    const CREATED_AT = 'createdAt';
    const UPDATED_AT = 'updatedAt';
    const DELETED_AT = 'deletedAt';

    protected $casts = [
        'moderated_at' => 'datetime',
        'flagged_at' => 'datetime',
        'is_flagged' => 'boolean',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function admin(): BelongsTo
    {
        return $this->belongsTo(User::class, 'admin_id');
    }
}
