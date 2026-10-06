<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Payment extends Model
{
    public const METHOD_CASH = 'cash';

    public const METHOD_UPI = 'upi';

    public const METHOD_CARD = 'card';

    public const METHOD_COD = 'cod';

    public const STATUS_PAID = 'paid';

    public const STATUS_PENDING = 'pending';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'order_id',
        'method',
        'amount',
        'status',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
        ];
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function methodLabel(): string
    {
        return match ($this->method) {
            self::METHOD_CASH => 'Cash',
            self::METHOD_UPI => 'UPI',
            self::METHOD_CARD => 'Card',
            self::METHOD_COD => 'Cash on Delivery',
            default => $this->method,
        };
    }

    public function statusLabel(): string
    {
        return match ($this->status) {
            self::STATUS_PAID => 'Paid',
            self::STATUS_PENDING => 'Pending',
            default => ucfirst((string) $this->status),
        };
    }
}
