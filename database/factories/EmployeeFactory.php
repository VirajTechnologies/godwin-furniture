<?php

namespace Database\Factories;

use App\Models\Employee;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Employee>
 */
class EmployeeFactory extends Factory
{
    protected $model = Employee::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'employee_code' => strtoupper(fake()->unique()->bothify('EMP###')),
            'warehouse_id' => null,
            'branch_id' => null,
            'designation' => 'Cashier',
            'status' => Employee::STATUS_ACTIVE,
        ];
    }
}
