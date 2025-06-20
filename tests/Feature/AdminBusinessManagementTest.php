<?php

namespace Tests\Feature;

use App\Models\Business;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class AdminBusinessManagementTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->admin = User::factory()->create(['is_admin' => true]);
    }

    #[Test]
    public function admin_can_view_dashboard_with_pending_businesses()
    {
        $pendingBusiness = Business::factory()->create(['status' => 'pending']);
        $approvedBusiness = Business::factory()->create(['status' => 'approved']);

        $response = $this->actingAs($this->admin)
            ->get(route('admin.dashboard'));

        $response->assertStatus(200)
            ->assertViewIs('admin.dashboard')
            ->assertSee('Admin Dashboard')
            ->assertSee('Pending Approval')
            ->assertSee($pendingBusiness->business_name)
            ->assertDontSee($approvedBusiness->business_name);
    }

    #[Test]
    public function admin_dashboard_shows_business_statistics()
    {
        Business::factory()->count(3)->create(['status' => 'pending']);
        Business::factory()->count(2)->create(['status' => 'approved']);
        Business::factory()->count(1)->create(['status' => 'rejected']);

        $response = $this->actingAs($this->admin)
            ->get(route('admin.dashboard'));

        $response->assertStatus(200)
            ->assertSee('3')  // pending count
            ->assertSee('2')  // approved count
            ->assertSee('1'); // rejected count
    }

    #[Test]
    public function admin_can_approve_pending_business()
    {
        $business = Business::factory()->create(['status' => 'pending']);

        $response = $this->actingAs($this->admin)
            ->patch(route('admin.businesses.approve', $business->business_slug));

        $response->assertRedirect(route('admin.dashboard'))
            ->assertSessionHas('success', 'Business approved successfully!');

        $this->assertDatabaseHas('businesses', [
            'id' => $business->id,
            'status' => 'approved',
        ]);
    }

    #[Test]
    public function admin_can_reject_pending_business()
    {
        $business = Business::factory()->create(['status' => 'pending']);

        $response = $this->actingAs($this->admin)
            ->patch(route('admin.businesses.reject', $business->business_slug), [
                'rejection_reason' => 'Incomplete information provided',
            ]);

        $response->assertRedirect(route('admin.dashboard'))
            ->assertSessionHas('success', 'Business rejected successfully!');

        $this->assertDatabaseHas('businesses', [
            'id' => $business->id,
            'status' => 'rejected',
        ]);
    }

    #[Test]
    public function admin_can_view_business_details()
    {
        $business = Business::factory()->create([
            'status' => 'pending',
            'business_name' => 'Test Business Details',
            'description' => 'Detailed business description',
        ]);

        $response = $this->actingAs($this->admin)
            ->get(route('admin.businesses.show', $business->business_slug));

        $response->assertStatus(200)
            ->assertViewIs('admin.businesses.show')
            ->assertSee('Test Business Details')
            ->assertSee('Detailed business description')
            ->assertSee('Approve')
            ->assertSee('Reject');
    }

    #[Test]
    public function admin_cannot_approve_already_approved_business()
    {
        $business = Business::factory()->create(['status' => 'approved']);

        $response = $this->actingAs($this->admin)
            ->patch(route('admin.businesses.approve', $business->business_slug));

        $response->assertRedirect(route('admin.dashboard'))
            ->assertSessionHas('error', 'Business is not pending approval.');
    }

    #[Test]
    public function admin_cannot_reject_already_approved_business()
    {
        $business = Business::factory()->create(['status' => 'approved']);

        $response = $this->actingAs($this->admin)
            ->patch(route('admin.businesses.reject', $business->business_slug), [
                'rejection_reason' => 'Test reason',
            ]);

        $response->assertRedirect(route('admin.dashboard'))
            ->assertSessionHas('error', 'Business is not pending approval.');
    }

    #[Test]
    public function rejection_requires_reason()
    {
        $business = Business::factory()->create(['status' => 'pending']);

        $response = $this->actingAs($this->admin)
            ->patch(route('admin.businesses.reject', $business->business_slug), []);

        $response->assertRedirect()
            ->assertSessionHasErrors(['rejection_reason']);

        $this->assertDatabaseHas('businesses', [
            'id' => $business->id,
            'status' => 'pending', // Should remain unchanged
        ]);
    }

    #[Test]
    public function admin_can_toggle_business_featured_status()
    {
        $business = Business::factory()->create([
            'status' => 'approved',
            'is_featured' => false,
        ]);

        $response = $this->actingAs($this->admin)
            ->patch(route('admin.businesses.toggle-featured', $business->business_slug));

        $response->assertRedirect()
            ->assertSessionHas('success', 'Featured status updated successfully!');

        $this->assertDatabaseHas('businesses', [
            'id' => $business->id,
            'is_featured' => true,
        ]);
    }

    #[Test]
    public function admin_can_toggle_business_verified_status()
    {
        $business = Business::factory()->create([
            'status' => 'approved',
            'is_verified' => false,
        ]);

        $response = $this->actingAs($this->admin)
            ->patch(route('admin.businesses.toggle-verified', $business->business_slug));

        $response->assertRedirect()
            ->assertSessionHas('success', 'Verified status updated successfully!');

        $this->assertDatabaseHas('businesses', [
            'id' => $business->id,
            'is_verified' => true,
        ]);

        $this->assertDatabaseMissing('businesses', [
            'id' => $business->id,
            'verified_at' => null,
        ]);
    }

    #[Test]
    public function guest_cannot_access_admin_business_routes()
    {
        $business = Business::factory()->create();

        $routes = [
            ['GET', route('admin.dashboard')],
            ['GET', route('admin.businesses.show', $business->business_slug)],
            ['PATCH', route('admin.businesses.approve', $business->business_slug)],
            ['PATCH', route('admin.businesses.reject', $business->business_slug)],
            ['PATCH', route('admin.businesses.toggle-featured', $business->business_slug)],
            ['PATCH', route('admin.businesses.toggle-verified', $business->business_slug)],
        ];

        foreach ($routes as [$method, $route]) {
            $response = $this->call($method, $route);
            $response->assertRedirect(route('admin.login'));
        }
    }

    #[Test]
    public function non_admin_cannot_access_admin_business_routes()
    {
        $user = User::factory()->create(['is_admin' => false]);
        $business = Business::factory()->create();

        $routes = [
            ['GET', route('admin.dashboard')],
            ['GET', route('admin.businesses.show', $business->business_slug)],
            ['PATCH', route('admin.businesses.approve', $business->business_slug)],
            ['PATCH', route('admin.businesses.reject', $business->business_slug)],
            ['PATCH', route('admin.businesses.toggle-featured', $business->business_slug)],
            ['PATCH', route('admin.businesses.toggle-verified', $business->business_slug)],
        ];

        foreach ($routes as [$method, $route]) {
            $response = $this->actingAs($user)->call($method, $route);
            $response->assertStatus(403);
        }
    }
} 