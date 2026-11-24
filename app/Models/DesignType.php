<?php

namespace App\Models;

use App\Models\Concerns\HasActiveStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DesignType extends Model
{
    use HasActiveStatus, HasFactory;

    /** @var list<string> */
    protected $fillable = [
        'catalog_product_category_id',
        'name',
        'status',
        'sort_order',
    ];

    public function productCategory(): BelongsTo
    {
        return $this->belongsTo(CatalogProductCategory::class, 'catalog_product_category_id');
    }
}
