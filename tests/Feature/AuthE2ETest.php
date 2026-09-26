<?php

namespace Tests\Feature;

use App\Models\Company;
use App\Models\Branch;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class AuthE2ETest extends TestCase
{
    use DatabaseTransactions;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $company = Company::create([
            'name' => 'Auth Test Company',
            'code' => 'ATC',
            'is_active' => true,
        ]);

        $branch = Branch::create([
            'company_id' => $company->id,
            'name' => 'Main Branch',
            'code' => 'MB01',
            'is_active' => true,
        ]);

        $this->user = User::create([
            'company_id' => $company->id,
            'branch_id' => $branch->id,
            'name' => 'Test User',
            'email' => 'authtest_' . time() . '@example.com',
            'password' => bcrypt('secret123'),
            'is_active' => true,
        ]);
    }

    public function test_login_screen_can_be_rendered(): void
    {
        $response = $this->get(route('login'));
        $response->assertStatus(200);
    }

    public function test_user_can_authenticate_with_valid_credentials(): void
    {
        $response = $this->post(route('login.post'), [
            'email' => $this->user->email,
            'password' => 'secret123',
        ]);

        $this->assertAuthenticatedAs($this->user);
        $response->assertRedirect(route('dashboard'));
    }

    public function test_user_cannot_authenticate_with_invalid_password(): void
    {
        $response = $this->post(route('login.post'), [
            'email' => $this->user->email,
            'password' => 'wrong-password',
        ]);

        $this->assertGuest();
        $response->assertSessionHasErrors();
    }

    public function test_user_can_logout(): void
    {
        $response = $this->actingAs($this->user)->post(route('logout'));

        $this->assertGuest();
        $response->assertRedirect(route('login'));
    }

    public function test_user_can_view_profile(): void
    {
        $response = $this->actingAs($this->user)->get(route('profile'));
        $response->assertStatus(200);
    }

    public function test_user_can_update_profile(): void
    {
        $response = $this->actingAs($this->user)->put(route('profile.update'), [
            'name' => 'Updated Name',
            'email' => $this->user->email,
            'phone' => '9876543210',
        ]);

        $response->assertRedirect();
        $this->assertEquals('Updated Name', $this->user->fresh()->name);
    }
}
