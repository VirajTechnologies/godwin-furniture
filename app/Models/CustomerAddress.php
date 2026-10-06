<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CustomerAddress extends Model
{
    /**
     * @var list<string>
     */
    protected $fillable = [
        'customer_id',
        'label',
        'address_line',
        'city',
        'pincode',
        'is_default',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_default' => 'boolean',
        ];
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function displayLabel(): string
    {
        return $this->label ?: 'Address';
    }

    public function oneLine(): string
    {
        return collect([$this->address_line, $this->city, $this->pincode])
            ->filter()
            ->implode(', ');
    }
}
