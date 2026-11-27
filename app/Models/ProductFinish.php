<?php

namespace App\Models;

use App\Models\Concerns\HasActiveStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductFinish extends Model
{
    use HasActiveStatus, HasFactory;

    /** @var list<string> */
    protected $fillable = [
        'name',
        'status',
        'sort_order',
    ];
}
