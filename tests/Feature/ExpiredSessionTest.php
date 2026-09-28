<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Contracts\Debug\ExceptionHandler;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Session\TokenMismatchException;
use Tests\TestCase;

class ExpiredSessionTest extends TestCase
{
    use RefreshDatabase;

    public function test_csrf_token_endpoint_returns_the_current_token(): void
    {
        $response = $this->getJson(route('csrf.token'));

        $response->assertOk()
            ->assertJson([
                'token' => csrf_token(),
                'authenticated' => false,
            ])
            ->assertHeader('Cache-Control', 'no-store, private');
    }

    public function test_remembered_admin_is_restored_when_the_idle_session_is_gone(): void
    {
        $admin = User::factory()->create([
            'role' => User::ROLE_SUPER_ADMIN,
            'status' => 'active',
        ]);

        $this->post(route('admin.login.store'), [
            'email' => $admin->email,
            'password' => 'password',
            'remember' => '1',
        ])->assertRedirect(route('admin.dashboard'));

        $this->flushSession();

        $this->getJson(route('csrf.token'))
            ->assertOk()
            ->assertJsonPath('authenticated', true);
    }

    public function test_admin_pages_keep_the_session_alive_while_open(): void
    {
        $admin = User::factory()->create([
            'role' => User::ROLE_SUPER_ADMIN,
            'status' => 'active',
        ]);

        $this->actingAs($admin)
            ->get(route('admin.dashboard'))
            ->assertOk()
            ->assertSee('setInterval', false)
            ->assertSee(route('csrf.token', absolute: false), false);
    }

    public function test_login_page_refreshes_the_token_before_submit(): void
    {
        $this->get(route('admin.login'))
            ->assertOk()
            ->assertSee(route('csrf.token', absolute: false), false);
    }

    public function test_expired_login_returns_to_the_sign_in_page(): void
    {
        $request = Request::create(route('admin.login.store'), 'POST', [
            'email' => 'admin@godwin.test',
            'password' => 'secret-password',
            '_token' => 'stale-token',
        ]);
        $request->headers->set('referer', route('admin.login'));

        $response = $this->app->make(ExceptionHandler::class)->render(
            $request,
            new TokenMismatchException('CSRF token mismatch.')
        );

        $this->assertSame(302, $response->getStatusCode());
        $this->assertSame(route('admin.login'), $response->headers->get('Location'));
        $this->assertSame('Your session expired. Please sign in again.', session('error'));
        $this->assertSame('admin@godwin.test', session()->getOldInput('email'));
        $this->assertNull(session()->getOldInput('password'));
    }
}
