<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class StockRequest extends Model
{
    public const STATUS_REQUESTED = 'requested';

    public const STATUS_CANCELLED = 'cancelled';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'code',
        'branch_id',
        'status',
        'notes',
        'requested_by',
        'stock_transfer_id',
        'cancelled_at',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'cancelled_at' => 'datetime',
        ];
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function requester(): BelongsTo
    {
        return $this->belongsTo(User::class, 'requested_by');
    }

    public function transfer(): BelongsTo
    {
        return $this->belongsTo(StockTransfer::class, 'stock_transfer_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(StockRequestItem::class);
    }

    public function isRequested(): bool
    {
        return $this->status === self::STATUS_REQUESTED;
    }

    public function label(): string
    {
        if ($this->status === self::STATUS_CANCELLED) {
            return 'Cancelled';
        }

        return match ($this->transfer?->status) {
            StockTransfer::STATUS_DRAFT => 'Draft Transfer',
            StockTransfer::STATUS_DISPATCHED => 'Dispatched',
            StockTransfer::STATUS_RECEIVED => 'Received',
            default => 'Requested',
        };
    }
}
