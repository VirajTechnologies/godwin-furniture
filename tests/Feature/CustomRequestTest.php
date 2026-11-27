<?php

namespace Tests\Feature;

use App\Models\Agent;
use App\Models\Branch;
use App\Models\City;
use App\Models\CustomRequest;
use App\Models\District;
use App\Models\Employee;
use App\Models\Product;
use App\Models\Role;
use App\Models\State;
use App\Models\User;
use App\Models\Warehouse;
use App\Support\Catalog;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\CreatesCatalog;
use Tests\TestCase;

class CustomRequestTest extends TestCase
{
    use CreatesCatalog;
    use RefreshDatabase;

    public function test_agent_can_send_a_matched_custom_request_to_the_primary_branch(): void
    {
        [$agent, $branch] = $this->fixture();
        $model = Catalog::modelsFor('Sofa')[0];
        $type = Catalog::typesFor('Sofa')[0];

        $this->actingAs($agent->user)
            ->post(route('agent.custom-requests.store'), [
                'project_category' => 'Furniture',
                'room' => 'Living Room',
                'product_category' => 'Sofa',
                'design_type' => $type,
                'model_name' => $model,
                'description' => 'Three-seater with deeper seat.',
                'width_cm' => 210,
                'depth_cm' => 95,
                'height_cm' => 90,
                'weight_kg' => 55,
                'variants' => [
                    [
                        'colour' => 'Walnut',
                        'finish' => 'Matte',
                        'upholstery' => 'Velvet',
                    ],
                ],
                'customer_name' => 'Meena Rao',
                'customer_phone' => '9848012001',
                'notes' => 'Need walnut finish and longer armrests.',
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('custom_requests', [
            'agent_id' => $agent->id,
            'branch_id' => $branch->id,
            'match_mode' => CustomRequest::MODE_MATCHED,
            'model_name' => $model,
            'customer_name' => 'Meena Rao',
            'status' => CustomRequest::STATUS_REQUESTED,
        ]);
    }

    public function test_agent_can_send_an_unmatched_custom_request_with_minimum_details(): void
    {
        [$agent, $branch] = $this->fixture();

        $this->actingAs($agent->user)
            ->post(route('agent.custom-requests.store'), [
                'model_not_listed' => '1',
                'model_name' => 'Bespoke Marble Console',
                'customer_name' => 'Harika Devi',
                'notes' => 'Prefer ivory marble top if available.',
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('custom_requests', [
            'agent_id' => $agent->id,
            'branch_id' => $branch->id,
            'match_mode' => CustomRequest::MODE_UNMATCHED,
            'product_id' => null,
            'model_name' => 'Bespoke Marble Console',
            'status' => CustomRequest::STATUS_REQUESTED,
        ]);
    }

    public function test_admin_and_primary_branch_can_accept_a_custom_request(): void
    {
        [$agent, $branch] = $this->fixture();
        $admin = User::factory()->superAdmin()->create();
        $manager = $this->branchManager($branch);

        $this->actingAs($agent->user)
            ->post(route('agent.custom-requests.store'), [
                'model_not_listed' => '1',
                'model_name' => 'Custom height dining table',
                'notes' => 'Custom height for dining table.',
            ])
            ->assertRedirect();

        $customRequest = CustomRequest::query()->firstOrFail();

        $this->actingAs($admin)
            ->post(route('admin.custom-requests.status', $customRequest), [
                'status' => CustomRequest::STATUS_ACCEPTED,
            ])
            ->assertRedirect()
            ->assertSessionHas('success');

        $this->assertSame(CustomRequest::STATUS_ACCEPTED, $customRequest->fresh()->status);

        $this->actingAs($manager)
            ->post(route('branch.custom-requests.status', $customRequest), [
                'status' => CustomRequest::STATUS_IN_PROGRESS,
            ])
            ->assertRedirect()
            ->assertSessionHas('success');

        $this->assertSame(CustomRequest::STATUS_IN_PROGRESS, $customRequest->fresh()->status);
    }

    public function test_non_primary_branch_cannot_open_custom_requests(): void
    {
        [$agent, $primary] = $this->fixture();
        $other = Branch::query()->create([
            'code' => 'BR002',
            'name' => 'Guntur Showroom',
            'warehouse_id' => $primary->warehouse_id,
            'address_line' => 'Main Road',
            'state_id' => $primary->state_id,
            'district_id' => $primary->district_id,
            'city_id' => $primary->city_id,
            'pincode' => '522001',
            'is_primary' => false,
            'status' => 'active',
        ]);
        $manager = $this->branchManager($other, 'BM002');

        $this->actingAs($agent->user)
            ->post(route('agent.custom-requests.store'), [
                'model_not_listed' => '1',
                'model_name' => 'Custom legs stool',
                'notes' => 'Need custom legs.',
            ])
            ->assertRedirect();

        $this->actingAs($manager)
            ->get(route('branch.custom-requests.index'))
            ->assertForbidden();
    }

    /**
     * @return array{0: Agent, 1: Branch, 2: Product}
     */
    private function fixture(): array
    {
        $state = State::query()->create(['state_name' => 'Andhra Pradesh', 'status' => 'active']);
        $district = District::query()->create([
            'state_id' => $state->id,
            'district_name' => 'Krishna',
            'status' => 'active',
        ]);
        $city = City::query()->create([
            'state_id' => $state->id,
            'district_id' => $district->id,
            'city_name' => 'Vijayawada',
            'status' => 'active',
        ]);
        $warehouse = Warehouse::query()->create([
            'code' => 'WH001',
            'name' => 'Main Warehouse',
            'address_line' => 'Industrial Area',
            'state_id' => $state->id,
            'district_id' => $district->id,
            'city_id' => $city->id,
            'pincode' => '520001',
            'is_primary' => true,
            'status' => 'active',
        ]);
        $branch = Branch::query()->create([
            'code' => 'BR001',
            'name' => 'Vijayawada Showroom',
            'warehouse_id' => $warehouse->id,
            'address_line' => 'MG Road',
            'state_id' => $state->id,
            'district_id' => $district->id,
            'city_id' => $city->id,
            'pincode' => '520010',
            'is_primary' => true,
            'status' => 'active',
        ]);
        $user = User::factory()->agent()->create([
            'name' => 'Venkateswara Rao',
            'email' => 'manager.lakshmi@godwin.test',
        ]);
        $agent = Agent::query()->create([
            'code' => 'AG001',
            'name' => 'Sri Lakshmi Furniture Mart',
            'user_id' => $user->id,
            'email' => 'manager.lakshmi@godwin.test',
            'address_line' => 'Main Road',
            'state_id' => $state->id,
            'district_id' => $district->id,
            'city_id' => $city->id,
            'pincode' => '520001',
            'status' => 'active',
        ]);
        $user->update(['agent_id' => $agent->id]);

        $product = $this->catalogProduct([
            'code' => 'SF001',
            'slug' => 'sf001',
            'name' => 'Three Seater Sofa',
            'base_price' => 45000,
            'is_online' => false,
        ]);

        return [$agent->fresh('user'), $branch, $product];
    }

    private function branchManager(Branch $branch, string $code = 'BM001'): User
    {
        $role = Role::query()->firstOrCreate(
            ['slug' => Role::BRANCH_MANAGER],
            ['name' => 'Branch Manager', 'status' => 'active'],
        );
        $user = User::factory()->create([
            'role_id' => $role->id,
            'status' => 'active',
        ]);
        Employee::query()->create([
            'user_id' => $user->id,
            'employee_code' => $code,
            'branch_id' => $branch->id,
            'designation' => 'Branch Manager',
            'status' => 'active',
        ]);

        return $user->fresh('employee.branch');
    }
}
