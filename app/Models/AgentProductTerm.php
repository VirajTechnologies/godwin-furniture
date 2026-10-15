<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AgentProductTerm extends Model
{
    /**
     * @var list<string>
     */
    protected $fillable = [
        'agent_id',
        'product_id',
        'minimum_price',
        'commission_percent',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'minimum_price' => 'decimal:2',
            'commission_percent' => 'decimal:2',
        ];
    }

    public function agent(): BelongsTo
    {
        return $this->belongsTo(Agent::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
