<?php

namespace App\Models;

use App\Models\Concerns\HasActiveStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class CatalogRoom extends Model
{
    use HasActiveStatus, HasFactory;

    /** @var list<string> */
    protected $fillable = [
        'project_category_id',
        'name',
        'slug',
        'status',
        'show_on_store',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'show_on_store' => 'boolean',
        ];
    }

    protected static function booted(): void
    {
        static::saving(function (CatalogRoom $room): void {
            if ($room->slug === null || $room->slug === '') {
                $room->slug = Str::slug($room->name);
            }
        });
    }

    public function projectCategory(): BelongsTo
    {
        return $this->belongsTo(ProjectCategory::class);
    }

    public function productCategories(): HasMany
    {
        return $this->hasMany(CatalogProductCategory::class)->orderBy('sort_order')->orderBy('name');
    }
}
