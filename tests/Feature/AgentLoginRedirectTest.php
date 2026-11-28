<?php

namespace Tests\Feature;

use App\Models\Agent;
use App\Models\City;
use App\Models\District;
use App\Models\State;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AgentLoginRedirectTest extends TestCase
{
    use RefreshDatabase;

    public function test_agent_login_ignores_admin_intended_url(): void
    {
        $agent = $this->shop();

        $this->get(route('admin.dashboard'));
        $this->assertTrue(session()->has('url.intended'));

        $this->post(route('agent.login.store'), [
            'email' => $agent->user->email,
            'password' => 'password',
        ])->assertRedirect(route('agent.home'));
    }

    public function test_logged_in_agent_is_sent_to_agent_home_from_admin(): void
    {
        $agent = $this->shop();

        $this->actingAs($agent->user)
            ->get(route('admin.dashboard'))
            ->assertRedirect(route('agent.home'));
    }

    public function test_logged_in_agent_visiting_admin_login_goes_to_agent_home(): void
    {
        $agent = $this->shop();

        $this->actingAs($agent->user)
            ->get(route('admin.login'))
            ->assertRedirect(route('agent.home'));
    }

    private function shop(): Agent
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
        $user = User::factory()->agent()->create([
            'email' => 'manager.lakshmi@godwin.test',
            'password' => 'password',
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

        return $agent->fresh('user');
    }
}
