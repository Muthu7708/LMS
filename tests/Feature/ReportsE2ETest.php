<?php

namespace Tests\Feature;

use App\Models\Branch;
use App\Models\Company;
use App\Models\Customer;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class ReportsE2ETest extends TestCase
{
    use DatabaseTransactions;

    protected Company $company;
    protected Branch $branch;
    protected User $user;
    protected Customer $customer;

    protected function setUp(): void
    {
        parent::setUp();

        $this->company = Company::create([
            'name' => 'Report Co',
            'code' => 'REPCO',
            'is_active' => true,
        ]);

        $this->branch = Branch::create([
            'company_id' => $this->company->id,
            'name' => 'Main Branch',
            'code' => 'MB01',
            'is_active' => true,
        ]);

        $this->user = User::create([
            'company_id' => $this->company->id,
            'branch_id' => $this->branch->id,
            'name' => 'Report Manager',
            'email' => 'repmgr_' . time() . rand(100, 999) . '@test.com',
            'password' => bcrypt('password'),
            'is_active' => true,
        ]);

        $role = Role::firstOrCreate(['name' => 'super_admin', 'guard_name' => 'web']);
        $this->user->assignRole($role);

        $permission = Permission::firstOrCreate(['name' => 'report.loan', 'guard_name' => 'web']);
        $role->givePermissionTo($permission);

        $this->customer = Customer::create([
            'company_id' => $this->company->id,
            'branch_id' => $this->branch->id,
            'created_by' => $this->user->id,
            'customer_no' => 'CUST-' . rand(10000, 99999),
            'first_name' => 'Report',
            'last_name' => 'User',
            'mobile' => '87' . rand(10000000, 99999999),
            'status' => 'active',
        ]);
    }

    public function test_can_view_reports_index(): void
    {
        $response = $this->actingAs($this->user)->get(route('reports.index'));
        $response->assertStatus(200);
    }

    public function test_can_view_disbursement_report(): void
    {
        $response = $this->actingAs($this->user)->get(route('reports.disbursement'));
        $response->assertStatus(200);
    }

    public function test_can_view_collection_report(): void
    {
        $response = $this->actingAs($this->user)->get(route('reports.collection'));
        $response->assertStatus(200);
    }

    public function test_can_view_overdue_report(): void
    {
        $response = $this->actingAs($this->user)->get(route('reports.overdue'));
        $response->assertStatus(200);
    }

    public function test_can_view_portfolio_report(): void
    {
        $response = $this->actingAs($this->user)->get(route('reports.portfolio'));
        $response->assertStatus(200);
    }

    public function test_can_view_customer_statement_report(): void
    {
        $response = $this->actingAs($this->user)->get(route('reports.customer-statement', ['customer_id' => $this->customer->id]));
        $response->assertStatus(200);
    }

    public function test_can_view_npa_report(): void
    {
        $response = $this->actingAs($this->user)->get(route('reports.npa'));
        $response->assertStatus(200);
    }

    public function test_can_export_reports(): void
    {
        $this->actingAs($this->user)->get(route('reports.disbursement.pdf'))->assertStatus(200);
        $this->actingAs($this->user)->get(route('reports.disbursement.excel'))->assertStatus(200);
        $this->actingAs($this->user)->get(route('reports.collection.pdf'))->assertStatus(200);
        $this->actingAs($this->user)->get(route('reports.collection.excel'))->assertStatus(200);
        $this->actingAs($this->user)->get(route('reports.overdue.pdf'))->assertStatus(200);
        $this->actingAs($this->user)->get(route('reports.overdue.excel'))->assertStatus(200);
        $this->actingAs($this->user)->get(route('reports.customer-statement.pdf', $this->customer))->assertStatus(200);
    }
}
