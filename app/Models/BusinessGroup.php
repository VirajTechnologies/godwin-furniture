<?php

namespace App\Models;

use App\Models\Concerns\HasActiveStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class BusinessGroup extends Model
{
    use HasActiveStatus, HasFactory;

    /** @var list<string> */
    protected $fillable = [
        'name',
        'status',
        'sort_order',
    ];

    public function projectCategories(): HasMany
    {
        return $this->hasMany(ProjectCategory::class)->orderBy('sort_order')->orderBy('name');
    }
}
