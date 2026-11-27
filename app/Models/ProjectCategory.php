<?php

namespace App\Models;

use App\Models\Concerns\HasActiveStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ProjectCategory extends Model
{
    use HasActiveStatus, HasFactory;

    /** @var list<string> */
    protected $fillable = [
        'business_group_id',
        'name',
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

    public function businessGroup(): BelongsTo
    {
        return $this->belongsTo(BusinessGroup::class);
    }

    public function rooms(): HasMany
    {
        return $this->hasMany(CatalogRoom::class)->orderBy('sort_order')->orderBy('name');
    }
}
