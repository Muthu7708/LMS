<?php

namespace Tests\Feature;

use App\Models\Branch;
use App\Models\Company;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class UserManagementE2ETest extends TestCase
{
    use DatabaseTransactions;

    protected Company $company;
    protected Branch $branch;
    protected User $adminUser;
    protected Role $loanOfficerRole;

    protected function setUp(): void
    {
        parent::setUp();

        $this->company = Company::create([
            'name' => 'User Mgmt Co',
            'code' => 'UMC',
            'is_active' => true,
        ]);

        $this->branch = Branch::create([
            'company_id' => $this->company->id,
            'name' => 'Central Branch',
            'code' => 'CB01',
            'is_active' => true,
        ]);

        $this->adminUser = User::create([
            'company_id' => $this->company->id,
            'branch_id' => $this->branch->id,
            'name' => 'Admin Manager',
            'email' => 'usermgmtadmin_' . time() . '@test.com',
            'password' => bcrypt('password'),
            'is_active' => true,
        ]);

        $adminRole = Role::firstOrCreate(['name' => 'super_admin', 'guard_name' => 'web']);
        $this->loanOfficerRole = Role::firstOrCreate(['name' => 'loan_officer', 'guard_name' => 'web']);
        $this->adminUser->assignRole($adminRole);

        $permissions = ['user.view', 'user.create', 'user.edit', 'user.delete'];
        foreach ($permissions as $perm) {
            Permission::firstOrCreate(['name' => $perm]);
            $adminRole->givePermissionTo($perm);
        }
    }

    public function test_can_list_users(): void
    {
        $response = $this->actingAs($this->adminUser)->get(route('users.index'));
        $response->assertStatus(200);
    }

    public function test_can_render_user_create_form(): void
    {
        $response = $this->actingAs($this->adminUser)->get(route('users.create'));
        $response->assertStatus(200);
    }

    public function test_can_create_user(): void
    {
        $phone = '9888' . rand(100000, 999999);
        $response = $this->actingAs($this->adminUser)->post(route('users.store'), [
            'name' => 'New Loan Officer',
            'email' => 'officer_' . time() . rand(100, 999) . '@test.com',
            'phone' => $phone,
            'password' => 'secret1234',
            'password_confirmation' => 'secret1234',
            'branch_id' => $this->branch->id,
            'role' => $this->loanOfficerRole->name,
        ]);

        $response->assertRedirect(route('users.index'));
        $createdUser = User::where('phone', $phone)->first();
        $this->assertNotNull($createdUser);
        $this->assertEquals('New Loan Officer', $createdUser->name);
    }

    public function test_can_show_user_details(): void
    {
        $targetUser = User::create([
            'company_id' => $this->company->id,
            'branch_id' => $this->branch->id,
            'name' => 'Target User',
            'email' => 'target_' . time() . '@test.com',
            'password' => bcrypt('password'),
            'is_active' => true,
        ]);

        $response = $this->actingAs($this->adminUser)->get(route('users.show', $targetUser));
        $response->assertStatus(200);
    }

    public function test_can_update_user(): void
    {
        $targetUser = User::create([
            'company_id' => $this->company->id,
            'branch_id' => $this->branch->id,
            'name' => 'Old User Name',
            'email' => 'old_' . time() . '@test.com',
            'password' => bcrypt('password'),
            'is_active' => true,
        ]);

        $response = $this->actingAs($this->adminUser)->put(route('users.update', $targetUser), [
            'name' => 'Updated User Name',
            'email' => $targetUser->email,
            'phone' => '9111111111',
            'branch_id' => $this->branch->id,
            'role' => $this->loanOfficerRole->name,
        ]);

        $response->assertRedirect(route('users.index'));
        $this->assertEquals('Updated User Name', $targetUser->fresh()->name);
    }

    public function test_can_toggle_user_active_status(): void
    {
        $targetUser = User::create([
            'company_id' => $this->company->id,
            'branch_id' => $this->branch->id,
            'name' => 'Toggle User',
            'email' => 'toggle_' . time() . '@test.com',
            'password' => bcrypt('password'),
            'is_active' => true,
        ]);

        $response = $this->actingAs($this->adminUser)->patch(route('users.toggle-active', $targetUser));
        $response->assertRedirect();
        $this->assertFalse($targetUser->fresh()->is_active);

        // Toggle back to active
        $this->actingAs($this->adminUser)->patch(route('users.toggle-active', $targetUser));
        $this->assertTrue($targetUser->fresh()->is_active);
    }

    public function test_user_cannot_deactivate_self(): void
    {
        $response = $this->actingAs($this->adminUser)->patch(route('users.toggle-active', $this->adminUser));
        $response->assertRedirect();
        $this->assertTrue($this->adminUser->fresh()->is_active);
    }

    public function test_can_delete_user(): void
    {
        $targetUser = User::create([
            'company_id' => $this->company->id,
            'branch_id' => $this->branch->id,
            'name' => 'Delete User',
            'email' => 'delete_' . time() . '@test.com',
            'password' => bcrypt('password'),
            'is_active' => true,
        ]);

        $response = $this->actingAs($this->adminUser)->delete(route('users.destroy', $targetUser));
        $response->assertRedirect(route('users.index'));
        $this->assertSoftDeleted($targetUser);
    }
}
