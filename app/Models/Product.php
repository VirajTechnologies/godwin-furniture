<?php

namespace App\Models;

use App\Models\Concerns\HasActiveStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
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
        'project_category',
        'room',
        'product_category',
        'design_type',
        'family',
        'width_cm',
        'depth_cm',
        'height_cm',
        'weight_kg',
        'specs',
        'material',
        'unit',
        'base_price',
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
            'base_price' => 'decimal:2',
            'width_cm' => 'decimal:2',
            'depth_cm' => 'decimal:2',
            'height_cm' => 'decimal:2',
            'weight_kg' => 'decimal:2',
            'specs' => 'array',
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

    public function variants(): HasMany
    {
        return $this->hasMany(ProductVariant::class)->orderBy('id');
    }

    public function activeVariants(): HasMany
    {
        return $this->variants()->where('status', ProductVariant::STATUS_ACTIVE);
    }

    public function stocks(): HasManyThrough
    {
        return $this->hasManyThrough(Stock::class, ProductVariant::class, 'product_id', 'product_variant_id');
    }

    public function images(): HasMany
    {
        return $this->hasMany(ProductImage::class)->orderBy('sort_order')->orderBy('id');
    }

    /**
     * Shared gallery shown when a variant has no photos of its own.
     */
    public function sharedImages(): HasMany
    {
        return $this->images()->whereNull('product_variant_id');
    }

    /**
     * Variant images first, then the shared product gallery.
     *
     * @return \Illuminate\Support\Collection<int, ProductImage>
     */
    public function galleryFor(?ProductVariant $variant = null)
    {
        if ($variant !== null) {
            $variant->loadMissing('images');

            if ($variant->images->isNotEmpty()) {
                return $variant->images;
            }
        }

        if ($this->relationLoaded('images')) {
            return $this->images->whereNull('product_variant_id')->values();
        }

        return $this->sharedImages()->get();
    }

    /**
     * Lowest active variant price, falling back to the base price.
     */
    public function fromPrice(): float
    {
        $variants = $this->relationLoaded('variants')
            ? $this->variants->where('status', ProductVariant::STATUS_ACTIVE)
            : $this->activeVariants()->get();

        $min = $variants->min('price');

        return $min !== null ? (float) $min : (float) $this->base_price;
    }

    /**
     * "Furniture › Living Room › Sofa › Chester"
     */
    public function path(string $separator = ' › '): string
    {
        return collect([$this->project_category, $this->room, $this->product_category, $this->design_type])
            ->filter()
            ->implode($separator);
    }

    public function hasUpholstery(): bool
    {
        return \App\Support\Catalog::usesUpholstery($this->family);
    }

    public function primaryImageUrl(): ?string
    {
        $gallery = $this->galleryFor($this->relationLoaded('activeVariants')
            ? $this->activeVariants->first()
            : $this->activeVariants()->first());

        return $gallery->first()?->url
            ?? ($this->relationLoaded('images') ? $this->images->first()?->url : $this->images()->value('url'));
    }
}
