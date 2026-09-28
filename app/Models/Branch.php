<?php

namespace App\Models;

use App\Models\Concerns\HasActiveStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Branch extends Model
{
    use HasActiveStatus;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'code',
        'name',
        'warehouse_id',
        'contact_person',
        'phone',
        'email',
        'address_line',
        'state_id',
        'district_id',
        'city_id',
        'pincode',
        'status',
        'notes',
    ];

    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function state(): BelongsTo
    {
        return $this->belongsTo(State::class);
    }

    public function district(): BelongsTo
    {
        return $this->belongsTo(District::class);
    }

    public function city(): BelongsTo
    {
        return $this->belongsTo(City::class);
    }
}
