<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Voucher extends Model
{
    protected $table = 'voucher';
    
    const CREATED_AT = 'createdAt';
    const UPDATED_AT = 'updatedAt';
    const DELETED_AT = 'deletedAt';
    
    protected $fillable = [
        'code',
        'max_discount',
        'min_order_value',
        'quantity',
        'is_used',
        'start_date',
        'end_date',
        'type',
        'value',
        'order_id',
    ];

    protected $casts = [
        'max_discount' => 'integer',
        'min_order_value' => 'integer',
        'value' => 'integer',
        'is_used' => 'boolean',
        'start_date' => 'datetime',
        'end_date' => 'datetime',
        'createdAt' => 'datetime',
        'updatedAt' => 'datetime',
        'deletedAt' => 'datetime',
    ];

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function usages(): HasMany
    {
        return $this->hasMany(VoucherUsage::class);
    }

    /**
     * Kiểm tra voucher có còn hạn không
     */
    public function isExpired(): bool
    {
        return now()->isAfter($this->end_date);
    }

    /**
     * Kiểm tra voucher có còn số lượng không
     */
    public function isAvailable(): bool
    {
        return $this->getUsedCount() < $this->quantity;
    }

    /**
     * Lấy số lượng đã sử dụng
     */
    public function getUsedCount(): int
    {
        return $this->usages()->count();
    }

    /**
     * Lấy số lượng còn lại
     */
    public function getRemainingCount(): int
    {
        return max(0, $this->quantity - $this->getUsedCount());
    }

    /**
     * Kiểm tra voucher có hợp lệ không
     */
    public function isValid(): bool
    {
        return !$this->isExpired() && $this->isAvailable();
    }

    /**
     * Tính toán số tiền giảm giá
     */
    public function calculateDiscount(float $orderAmount): float
    {
        if ($this->type === 'PERCENTAGE') {
            $discount = ($orderAmount * $this->value) / 100;
            return min($discount, $this->max_discount);
        }
        
        return min($this->value, $orderAmount);
    }

    /**
     * Sử dụng voucher
     */
    public function useVoucher(int $userId, int $orderId, float $orderAmount): VoucherUsage
    {
        if (!$this->isValid()) {
            throw new \Exception('Voucher không hợp lệ hoặc đã hết hạn');
        }

        $discountAmount = $this->calculateDiscount($orderAmount);

        $usage = VoucherUsage::create([
            'voucher_id' => $this->id,
            'user_id' => $userId,
            'order_id' => $orderId,
            'discount_amount' => $discountAmount,
            'used_at' => now(),
        ]);

        return $usage;
    }
}
