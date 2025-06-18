<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class AdminAuthTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function admin_can_view_login_form()
    {
        $response = $this->get(route('admin.login'));

        $response->assertStatus(200)
            ->assertViewIs('admin.login')
            ->assertSee('Admin Login')
            ->assertSee('Email')
            ->assertSee('Password');
    }

    #[Test]
    public function admin_can_login_with_valid_credentials()
    {
        $admin = User::factory()->create([
            'email' => 'admin@example.com',
            'password' => Hash::make('password123'),
            'is_admin' => true,
        ]);

        $response = $this->post(route('admin.login.store'), [
            'email' => 'admin@example.com',
            'password' => 'password123',
        ]);

        $response->assertRedirect(route('admin.dashboard'))
            ->assertSessionHas('success', 'Welcome back, admin!');

        $this->assertAuthenticatedAs($admin);
    }

    #[Test]
    public function non_admin_user_cannot_login_to_admin()
    {
        $user = User::factory()->create([
            'email' => 'user@example.com',
            'password' => Hash::make('password123'),
            'is_admin' => false,
        ]);

        $response = $this->post(route('admin.login.store'), [
            'email' => 'user@example.com',
            'password' => 'password123',
        ]);

        $response->assertRedirect(route('admin.login'))
            ->assertSessionHasErrors(['email' => 'Invalid admin credentials.']);

        $this->assertGuest();
    }

    #[Test]
    public function admin_login_requires_valid_credentials()
    {
        $response = $this->post(route('admin.login.store'), [
            'email' => 'invalid@example.com',
            'password' => 'wrongpassword',
        ]);

        $response->assertRedirect(route('admin.login'))
            ->assertSessionHasErrors(['email']);

        $this->assertGuest();
    }

    #[Test]
    public function admin_login_validates_required_fields()
    {
        $response = $this->post(route('admin.login.store'), []);

        $response->assertRedirect(route('admin.login'))
            ->assertSessionHasErrors(['email', 'password']);
    }

    #[Test]
    public function authenticated_admin_can_logout()
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $this->actingAs($admin);

        $response = $this->post(route('admin.logout'));

        $response->assertRedirect(route('admin.login'))
            ->assertSessionHas('success', 'You have been logged out.');

        $this->assertGuest();
    }

    #[Test]
    public function authenticated_admin_cannot_view_login_form()
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $this->actingAs($admin);

        $response = $this->get(route('admin.login'));

        $response->assertRedirect(route('admin.dashboard'));
    }

    #[Test]
    public function guest_cannot_access_admin_dashboard()
    {
        $response = $this->get(route('admin.dashboard'));

        $response->assertRedirect(route('admin.login'));
    }

    #[Test]
    public function non_admin_user_cannot_access_admin_dashboard()
    {
        $user = User::factory()->create(['is_admin' => false]);
        $this->actingAs($user);

        $response = $this->get(route('admin.dashboard'));

        $response->assertStatus(403);
    }
} 