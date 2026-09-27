<?php

namespace App\Models;

use App\Models\Concerns\HasActiveStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class State extends Model
{
    use HasActiveStatus;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'state_name',
        'status',
    ];

    public function districts(): HasMany
    {
        return $this->hasMany(District::class);
    }
}
