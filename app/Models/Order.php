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

    public const STATUS_CONFIRMED = 'confirmed';

    public const STATUS_DISPATCHED = 'dispatched';

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
        'confirmed_at',
        'dispatched_at',
        'completed_at',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'total' => 'decimal:2',
            'confirmed_at' => 'datetime',
            'dispatched_at' => 'datetime',
            'completed_at' => 'datetime',
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

    public function isOnline(): bool
    {
        return $this->channel === self::CHANNEL_ONLINE;
    }

    public function statusLabel(): string
    {
        return match ($this->status) {
            self::STATUS_PLACED => 'Placed',
            self::STATUS_CONFIRMED => 'Confirmed',
            self::STATUS_DISPATCHED => 'Dispatched',
            self::STATUS_COMPLETED => 'Completed',
            default => ucfirst((string) $this->status),
        };
    }

    public function nextFulfilmentAction(): ?string
    {
        if (! $this->isOnline()) {
            return null;
        }

        return match ($this->status) {
            self::STATUS_PLACED => 'confirm',
            self::STATUS_CONFIRMED => 'dispatch',
            self::STATUS_DISPATCHED => 'complete',
            default => null,
        };
    }

    /**
     * @return list<array{key: string, label: string, at: \Illuminate\Support\Carbon|null, done: bool, current: bool}>
     */
    public function fulfilmentTimeline(): array
    {
        $steps = [
            [
                'key' => self::STATUS_PLACED,
                'label' => 'Placed',
                'at' => $this->created_at,
            ],
            [
                'key' => self::STATUS_CONFIRMED,
                'label' => 'Confirmed',
                'at' => $this->confirmed_at,
            ],
            [
                'key' => self::STATUS_DISPATCHED,
                'label' => 'Dispatched',
                'at' => $this->dispatched_at,
            ],
            [
                'key' => self::STATUS_COMPLETED,
                'label' => 'Completed',
                'at' => $this->completed_at,
            ],
        ];

        $order = [self::STATUS_PLACED, self::STATUS_CONFIRMED, self::STATUS_DISPATCHED, self::STATUS_COMPLETED];
        $currentIndex = array_search($this->status, $order, true);
        if ($currentIndex === false) {
            $currentIndex = 0;
        }

        return array_map(function (array $step, int $index) use ($currentIndex) {
            $done = $step['at'] !== null || $index < $currentIndex;
            $current = $index === $currentIndex;

            return [
                'key' => $step['key'],
                'label' => $step['label'],
                'at' => $step['at'],
                'done' => $done,
                'current' => $current,
            ];
        }, $steps, array_keys($steps));
    }
}
