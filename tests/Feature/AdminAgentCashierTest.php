<?php

namespace Tests\Feature;

use App\Models\Agent;
use App\Models\City;
use App\Models\District;
use App\Models\State;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AdminAgentCashierTest extends TestCase
{
    use RefreshDatabase;

    public function test_an_admin_can_update_a_cashier_and_an_inactive_cashier_cannot_sign_in(): void
    {
        $admin = User::factory()->superAdmin()->create();
        [$agent, $cashier, $other] = $this->shops();

        $this->actingAs($admin)
            ->get(route('admin.agents.edit', $agent))
            ->assertOk()
            ->assertSee('Save Cashier')
            ->assertSee('cashier.lakshmi@godwin.test');

        $this->actingAs($admin)
            ->put(route('admin.agents.cashiers.update', [$agent, $cashier]), [
                'cashier_form' => 'update',
                'cashier_id' => $cashier->id,
                'cashier_name' => 'Padmavathi Rao',
                'cashier_email' => 'padma.lakshmi@godwin.test',
                'cashier_phone' => '9848092001',
                'cashier_password' => 'new-password',
                'cashier_status' => 'inactive',
            ])
            ->assertRedirect(route('admin.agents.edit', $agent));

        $cashier->refresh();
        $this->assertSame('Padmavathi Rao', $cashier->name);
        $this->assertSame('padma.lakshmi@godwin.test', $cashier->email);
        $this->assertSame('9848092001', $cashier->phone);
        $this->assertSame('inactive', $cashier->status);
        $this->assertTrue(Hash::check('new-password', $cashier->password));

        auth()->logout();
        $this->flushSession();

        $this->post(route('agent.login.store'), [
            'email' => 'padma.lakshmi@godwin.test',
            'password' => 'new-password',
        ])->assertSessionHasErrors('email');

        $this->actingAs($admin)
            ->put(route('admin.agents.cashiers.update', [$agent, $other]), [
                'cashier_form' => 'update',
                'cashier_id' => $other->id,
                'cashier_name' => 'Other',
                'cashier_email' => 'cashier.kalanjali@godwin.test',
                'cashier_status' => 'active',
            ])
            ->assertNotFound();
    }

    /**
     * @return array{0: Agent, 1: User, 2: User}
     */
    private function shops(): array
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

        $agent = $this->agent('AG001', 'Sri Lakshmi Furniture Mart', 'manager.lakshmi@godwin.test', $state->id, $district->id, $city->id);
        $otherShop = $this->agent('AG002', 'Kalanjali Home Studio', 'manager.kalanjali@godwin.test', $state->id, $district->id, $city->id);
        $cashier = User::factory()->agentStaff()->create([
            'name' => 'Padmavathi',
            'email' => 'cashier.lakshmi@godwin.test',
            'agent_id' => $agent->id,
        ]);
        $other = User::factory()->agentStaff()->create([
            'name' => 'Ramesh Babu',
            'email' => 'cashier.kalanjali@godwin.test',
            'agent_id' => $otherShop->id,
        ]);

        return [$agent, $cashier, $other];
    }

    private function agent(string $code, string $shop, string $email, int $stateId, int $districtId, int $cityId): Agent
    {
        $user = User::factory()->agent()->create([
            'name' => $shop,
            'email' => $email,
        ]);
        $agent = Agent::query()->create([
            'code' => $code,
            'name' => $shop,
            'user_id' => $user->id,
            'email' => $email,
            'address_line' => 'Main Road',
            'state_id' => $stateId,
            'district_id' => $districtId,
            'city_id' => $cityId,
            'pincode' => '520001',
            'status' => 'active',
        ]);
        $user->update(['agent_id' => $agent->id]);

        return $agent;
    }
}
