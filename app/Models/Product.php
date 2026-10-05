<?php

namespace App\Models;

use App\Models\Concerns\HasActiveStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Product extends Model
{
    use HasActiveStatus, HasFactory;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'category_id',
        'code',
        'slug',
        'name',
        'description',
        'material',
        'unit',
        'selling_price',
        'compare_at_price',
        'online_price',
        'is_online',
        'is_featured',
        'status',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'selling_price' => 'decimal:2',
            'compare_at_price' => 'decimal:2',
            'online_price' => 'decimal:2',
            'is_online' => 'boolean',
            'is_featured' => 'boolean',
        ];
    }

    protected static function booted(): void
    {
        static::saving(function (Product $product): void {
            if ($product->slug === null || $product->slug === '') {
                $product->slug = Str::slug($product->name);
            }
        });
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function stocks(): HasMany
    {
        return $this->hasMany(Stock::class);
    }

    public function images(): HasMany
    {
        return $this->hasMany(ProductImage::class)->orderBy('sort_order')->orderBy('id');
    }

    public function branchPrices(): HasMany
    {
        return $this->hasMany(BranchProductPrice::class);
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

        return (float) $this->selling_price;
    }

    public function storePrice(): float
    {
        return $this->online_price !== null
            ? (float) $this->online_price
            : (float) $this->selling_price;
    }

    public function mrp(): ?float
    {
        return $this->compare_at_price !== null
            ? (float) $this->compare_at_price
            : null;
    }

    public function primaryImageUrl(): ?string
    {
        if ($this->relationLoaded('images')) {
            return $this->images->first()?->url;
        }

        return $this->images()->value('url');
    }
}
