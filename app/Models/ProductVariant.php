<?php

namespace App\Models;

use App\Models\Concerns\HasActiveStatus;
use App\Support\Catalog;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ProductVariant extends Model
{
    use HasActiveStatus, HasFactory;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'product_id',
        'sku',
        'colour',
        'colour_hex',
        'finish',
        'upholstery',
        'price',
        'compare_at_price',
        'status',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'compare_at_price' => 'decimal:2',
        ];
    }

    protected static function booted(): void
    {
        static::saving(function (ProductVariant $variant): void {
            $variant->upholstery = (string) $variant->upholstery;

            if ($variant->colour_hex === null || $variant->colour_hex === '') {
                $variant->colour_hex = Catalog::colourHex($variant->colour);
            }
        });
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function stocks(): HasMany
    {
        return $this->hasMany(Stock::class);
    }

    public function branchPrices(): HasMany
    {
        return $this->hasMany(BranchProductPrice::class);
    }

    public function agentTerms(): HasMany
    {
        return $this->hasMany(AgentProductTerm::class);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_ACTIVE);
    }

    /**
     * "Walnut · Matte · Velvet"
     */
    public function label(): string
    {
        return collect([$this->colour, $this->finish, $this->upholstery])
            ->filter(fn ($part) => $part !== null && $part !== '')
            ->implode(' · ');
    }

    /**
     * "Oslo Sofa — Walnut · Matte · Velvet"
     */
    public function displayName(): string
    {
        $name = $this->product?->name ?? 'Product';

        return $name.' — '.$this->label();
    }

    public function priceForBranch(Branch|int $branch): float
    {
        $branchId = $branch instanceof Branch ? $branch->id : $branch;

        if ($this->relationLoaded('branchPrices')) {
            $override = $this->branchPrices->firstWhere('branch_id', $branchId);
        } else {
            $override = $this->branchPrices()->where('branch_id', $branchId)->first();
        }

        if ($override !== null) {
            return (float) $override->price;
        }

        return (float) $this->price;
    }

    public function priceForAgent(Agent|int $agent): float
    {
        $term = $this->termForAgent($agent);

        if ($term !== null && $term->price !== null) {
            return (float) $term->price;
        }

        return (float) $this->price;
    }

    public function commissionPercentForAgent(Agent|int $agent): float
    {
        $percent = $this->termForAgent($agent)?->commission_percent;

        if ($percent !== null) {
            return (float) $percent;
        }

        if (! $agent instanceof Agent) {
            $agent = Agent::query()->find($agent);
        }

        return $agent?->commission_percent !== null ? (float) $agent->commission_percent : 0;
    }

    public function storePrice(): float
    {
        return (float) $this->price;
    }

    public function mrp(): ?float
    {
        return $this->compare_at_price !== null
            ? (float) $this->compare_at_price
            : null;
    }

    private function termForAgent(Agent|int $agent): ?AgentProductTerm
    {
        $agentId = $agent instanceof Agent ? $agent->id : $agent;

        if ($this->relationLoaded('agentTerms')) {
            return $this->agentTerms->firstWhere('agent_id', $agentId);
        }

        return $this->agentTerms()->where('agent_id', $agentId)->first();
    }
}
