<?php

namespace Database\Factories;

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends Factory<User>
 */
class UserFactory extends Factory
{
    /**
     * The current password being used by the factory.
     */
    protected static ?string $password;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'phone' => null,
            'email_verified_at' => now(),
            'password' => static::$password ??= Hash::make('password'),
            'status' => 'active',
            'remember_token' => Str::random(10),
        ];
    }

    /**
     * Attach the head-office role.
     */
    public function superAdmin(): static
    {
        return $this->state(function (): array {
            $role = Role::query()->firstOrCreate(
                ['slug' => Role::SUPER_ADMIN],
                ['name' => 'Super Admin', 'status' => Role::STATUS_ACTIVE],
            );

            return [
                'role_id' => $role->id,
                'status' => 'active',
            ];
        });
    }

    public function warehouseStaff(): static
    {
        return $this->withRole(Role::WAREHOUSE_STAFF, 'Warehouse Staff');
    }

    public function branchManager(): static
    {
        return $this->withRole(Role::BRANCH_MANAGER, 'Branch Manager');
    }

    public function branchStaff(): static
    {
        return $this->withRole(Role::BRANCH_STAFF, 'Branch Staff');
    }

    /**
     * Indicate that the model's email address should be unverified.
     */
    public function unverified(): static
    {
        return $this->state(fn (array $attributes) => [
            'email_verified_at' => null,
        ]);
    }

    private function withRole(string $slug, string $name): static
    {
        return $this->state(function () use ($slug, $name): array {
            $role = Role::query()->firstOrCreate(
                ['slug' => $slug],
                ['name' => $name, 'status' => Role::STATUS_ACTIVE],
            );

            return [
                'role_id' => $role->id,
                'status' => 'active',
            ];
        });
    }
}
