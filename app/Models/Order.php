<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Order extends Model
{
    public const CHANNEL_BRANCH = 'branch';

    public const CHANNEL_ONLINE = 'online';

    public const STATUS_COMPLETED = 'completed';

    public const STATUS_PLACED = 'placed';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'code',
        'channel',
        'customer_id',
        'branch_id',
        'warehouse_id',
        'placed_by',
        'total',
        'status',
        'notes',
        'shipping_address',
        'shipping_city',
        'shipping_pincode',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'total' => 'decimal:2',
        ];
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function seller(): BelongsTo
    {
        return $this->belongsTo(User::class, 'placed_by');
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function payment(): HasOne
    {
        return $this->hasOne(Payment::class);
    }

    public function statusLabel(): string
    {
        return match ($this->status) {
            self::STATUS_PLACED => 'Placed',
            self::STATUS_COMPLETED => 'Completed',
            default => ucfirst((string) $this->status),
        };
    }
}
