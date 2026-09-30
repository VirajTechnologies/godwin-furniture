<?php

namespace Database\Factories;

use App\Models\Branch;
use App\Models\StockTransfer;
use App\Models\User;
use App\Models\Warehouse;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<StockTransfer>
 */
class StockTransferFactory extends Factory
{
    protected $model = StockTransfer::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'code' => strtoupper(fake()->unique()->bothify('TR###')),
            'warehouse_id' => Warehouse::factory(),
            'branch_id' => Branch::factory(),
            'status' => StockTransfer::STATUS_DRAFT,
            'notes' => null,
            'created_by' => User::factory()->superAdmin(),
        ];
    }
}
