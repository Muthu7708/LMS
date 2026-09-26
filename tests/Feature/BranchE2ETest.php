<?php

namespace Tests\Feature;

use App\Models\Branch;
use App\Models\Company;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class BranchE2ETest extends TestCase
{
    use DatabaseTransactions;

    protected Company $company;
    protected User $adminUser;

    protected function setUp(): void
    {
        parent::setUp();

        $this->company = Company::create([
            'name' => 'Branch Test Co',
            'code' => 'BTC',
            'is_active' => true,
        ]);

        $mainBranch = Branch::create([
            'company_id' => $this->company->id,
            'name' => 'HQ Branch',
            'code' => 'HQ01',
            'is_active' => true,
        ]);

        $this->adminUser = User::create([
            'company_id' => $this->company->id,
            'branch_id' => $mainBranch->id,
            'name' => 'Admin Branch Manager',
            'email' => 'branchadmin_' . time() . '@test.com',
            'password' => bcrypt('password'),
            'is_active' => true,
        ]);

        $role = Role::firstOrCreate(['name' => 'super_admin']);
        $this->adminUser->assignRole($role);

        $permissions = ['branch.view', 'branch.create', 'branch.edit', 'branch.delete'];
        foreach ($permissions as $perm) {
            Permission::firstOrCreate(['name' => $perm]);
            $role->givePermissionTo($perm);
        }
    }

    public function test_can_list_branches(): void
    {
        $response = $this->actingAs($this->adminUser)->get(route('branches.index'));
        $response->assertStatus(200);
    }

    public function test_can_render_branch_create_form(): void
    {
        $response = $this->actingAs($this->adminUser)->get(route('branches.create'));
        $response->assertStatus(200);
    }

    public function test_can_create_new_branch(): void
    {
        $response = $this->actingAs($this->adminUser)->post(route('branches.store'), [
            'name' => 'North Branch',
            'code' => 'NB01',
            'city' => 'Delhi',
            'state' => 'Delhi',
            'phone' => '9811111111',
            'email' => 'delhi@test.com',
            'address' => 'Connaught Place',
            'is_active' => 1,
        ]);

        $branch = Branch::where('code', 'NB01')->first();
        $this->assertNotNull($branch);
        $this->assertEquals('North Branch', $branch->name);
        $response->assertRedirect(route('branches.index'));
    }

    public function test_can_show_branch_details(): void
    {
        $branch = Branch::create([
            'company_id' => $this->company->id,
            'name' => 'South Branch',
            'code' => 'SB01',
            'is_active' => true,
        ]);

        $response = $this->actingAs($this->adminUser)->get(route('branches.show', $branch));
        $response->assertStatus(200);
    }

    public function test_can_update_branch(): void
    {
        $branch = Branch::create([
            'company_id' => $this->company->id,
            'name' => 'Old Branch Name',
            'code' => 'OB01',
            'is_active' => true,
        ]);

        $response = $this->actingAs($this->adminUser)->put(route('branches.update', $branch), [
            'name' => 'Updated Branch Name',
            'code' => 'OB01',
            'city' => 'Pune',
            'state' => 'Maharashtra',
            'is_active' => 1,
        ]);

        $response->assertRedirect(route('branches.index'));
        $this->assertEquals('Updated Branch Name', $branch->fresh()->name);
    }

    public function test_can_delete_branch(): void
    {
        $branch = Branch::create([
            'company_id' => $this->company->id,
            'name' => 'Temp Branch',
            'code' => 'TB01',
            'is_active' => true,
        ]);

        $response = $this->actingAs($this->adminUser)->delete(route('branches.destroy', $branch));
        $response->assertRedirect(route('branches.index'));
        $this->assertSoftDeleted($branch);
    }
}
