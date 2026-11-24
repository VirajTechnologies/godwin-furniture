<?php

namespace App\Models;

use App\Models\Concerns\HasActiveStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class CatalogProductCategory extends Model
{
    use HasActiveStatus, HasFactory;

    /** @var list<string> */
    protected $fillable = [
        'catalog_room_id',
        'name',
        'slug',
        'status',
        'sort_order',
    ];

    protected static function booted(): void
    {
        static::saving(function (CatalogProductCategory $category): void {
            if ($category->slug === null || $category->slug === '') {
                $category->slug = Str::slug($category->name);
            }
        });
    }

    public function room(): BelongsTo
    {
        return $this->belongsTo(CatalogRoom::class, 'catalog_room_id');
    }

    public function designTypes(): HasMany
    {
        return $this->hasMany(DesignType::class)->orderBy('sort_order')->orderBy('name');
    }

    public function productModels(): HasMany
    {
        return $this->hasMany(ProductModel::class)->orderBy('sort_order')->orderBy('name');
    }
}
