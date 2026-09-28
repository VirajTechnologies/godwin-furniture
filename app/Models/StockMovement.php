<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StockMovement extends Model
{
    public const TYPE_OPENING = 'opening';

    public const TYPE_ADJUSTMENT = 'adjustment';

    public const TYPE_TRANSFER_OUT = 'transfer_out';

    public const TYPE_TRANSFER_IN = 'transfer_in';

    public const TYPE_SALE = 'sale';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'stock_id',
        'type',
        'quantity_change',
        'quantity_after',
        'note',
        'user_id',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'quantity_change' => 'integer',
            'quantity_after' => 'integer',
        ];
    }

    public function stock(): BelongsTo
    {
        return $this->belongsTo(Stock::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function label(): string
    {
        return match ($this->type) {
            self::TYPE_OPENING => 'Opening',
            self::TYPE_ADJUSTMENT => 'Adjustment',
            self::TYPE_TRANSFER_OUT => 'Transfer Out',
            self::TYPE_TRANSFER_IN => 'Transfer In',
            self::TYPE_SALE => 'Sale',
            default => $this->type,
        };
    }
}
