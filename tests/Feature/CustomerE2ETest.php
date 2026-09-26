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

class CustomerE2ETest extends TestCase
{
    use DatabaseTransactions;

    protected Company $company;
    protected Branch $branch;
    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->company = Company::create([
            'name' => 'Customer Co',
            'code' => 'CCO',
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
            'name' => 'Customer Manager',
            'email' => 'custmgr_' . time() . '@test.com',
            'password' => bcrypt('password'),
            'is_active' => true,
        ]);

        $role = Role::firstOrCreate(['name' => 'super_admin', 'guard_name' => 'web']);
        $this->user->assignRole($role);

        $permissions = [
            'customer.view', 'customer.create', 'customer.edit', 'customer.delete',
            'customer.blacklist', 'customer.unblacklist'
        ];
        foreach ($permissions as $perm) {
            Permission::firstOrCreate(['name' => $perm, 'guard_name' => 'web']);
            $role->givePermissionTo($perm);
        }
    }

    public function test_can_list_customers(): void
    {
        $response = $this->actingAs($this->user)->get(route('customers.index'));
        $response->assertStatus(200);
    }

    public function test_can_render_customer_create_form(): void
    {
        $response = $this->actingAs($this->user)->get(route('customers.create'));
        $response->assertStatus(200);
    }

    public function test_can_create_customer(): void
    {
        $this->withoutExceptionHandling();
        $uniqueMobile = '97' . rand(10000000, 99999999);
        $response = $this->actingAs($this->user)->post(route('customers.store'), [
            'first_name' => 'Robert',
            'last_name' => 'Smith',
            'mobile' => $uniqueMobile,
            'email' => 'robert_' . time() . rand(10, 99) . '@test.com',
            'gender' => 'male',
            'branch_id' => $this->branch->id,
            'address_type' => 'residential',
            'address_line1' => '123 Test Street',
            'city' => 'Mumbai',
            'state' => 'Maharashtra',
            'pin_code' => '400001',
        ]);

        $response->assertSessionHasNoErrors();
        $customer = Customer::where('mobile', $uniqueMobile)->first();
        $this->assertNotNull($customer);
        $response->assertRedirect(route('customers.show', $customer));
        $this->assertEquals('Robert', $customer->first_name);
        $this->assertCount(1, $customer->addresses);
    }

    public function test_can_show_customer(): void
    {
        $customer = Customer::create([
            'company_id' => $this->company->id,
            'branch_id' => $this->branch->id,
            'created_by' => $this->user->id,
            'customer_no' => 'CUST-9900' . rand(1, 9),
            'first_name' => 'Alice',
            'last_name' => 'Brown',
            'mobile' => '96' . rand(10000000, 99999999),
            'status' => 'active',
        ]);

        $response = $this->actingAs($this->user)->get(route('customers.show', $customer));
        $response->assertStatus(200);
    }

    public function test_can_update_customer(): void
    {
        $customer = Customer::create([
            'company_id' => $this->company->id,
            'branch_id' => $this->branch->id,
            'created_by' => $this->user->id,
            'customer_no' => 'CUST-TEST-02',
            'first_name' => 'Bob',
            'last_name' => 'Marley',
            'mobile' => '95' . rand(10000000, 99999999),
            'status' => 'active',
        ]);

        $response = $this->actingAs($this->user)->put(route('customers.update', $customer), [
            'first_name' => 'Robert',
            'last_name' => 'Marley',
            'mobile' => $customer->mobile,
            'email' => 'bobupdate@test.com',
        ]);

        $response->assertRedirect(route('customers.show', $customer));
        $this->assertEquals('Robert', $customer->fresh()->first_name);
    }

    public function test_can_blacklist_and_unblacklist_customer(): void
    {
        $customer = Customer::create([
            'company_id' => $this->company->id,
            'branch_id' => $this->branch->id,
            'created_by' => $this->user->id,
            'customer_no' => 'CUST-TEST-03',
            'first_name' => 'Charlie',
            'last_name' => 'Black',
            'mobile' => '94' . rand(10000000, 99999999),
            'status' => 'active',
        ]);

        // Blacklist
        $response = $this->actingAs($this->user)->post(route('customers.blacklist', $customer), [
            'reason' => 'Defaulter in secondary loan program',
        ]);
        $response->assertRedirect();
        $this->assertEquals('blacklisted', $customer->fresh()->status);

        // Unblacklist
        $response = $this->actingAs($this->user)->post(route('customers.unblacklist', $customer));
        $response->assertRedirect();
        $this->assertEquals('active', $customer->fresh()->status);
    }

    public function test_can_add_customer_sub_resources(): void
    {
        $customer = Customer::create([
            'company_id' => $this->company->id,
            'branch_id' => $this->branch->id,
            'created_by' => $this->user->id,
            'customer_no' => 'CUST-TEST-04',
            'first_name' => 'David',
            'last_name' => 'Sub',
            'mobile' => '93' . rand(10000000, 99999999),
            'status' => 'active',
        ]);

        // Add Address (office)
        $this->actingAs($this->user)->post(route('customers.addresses.store', $customer), [
            'type' => 'office',
            'address_line1' => 'Financial District',
            'city' => 'Mumbai',
            'state' => 'Maharashtra',
            'pin_code' => '400051',
        ]);
        $this->assertCount(1, $customer->fresh()->addresses);

        // Add Address (residential legacy/mapped type)
        $this->actingAs($this->user)->post(route('customers.addresses.store', $customer), [
            'type' => 'residential',
            'address_line1' => 'North Street',
            'city' => 'Chennai',
            'state' => 'Tamil Nadu',
            'pin_code' => '600092',
        ]);
        $this->assertCount(2, $customer->fresh()->addresses);

        // Add KYC
        $this->actingAs($this->user)->post(route('customers.kyc.store', $customer), [
            'document_type' => 'pan',
            'document_category' => 'identity',
            'document_number' => 'ABCDE1234F',
        ]);
        $this->assertCount(1, $customer->fresh()->kyc);

        // Add Employment
        $this->actingAs($this->user)->post(route('customers.employment.store', $customer), [
            'employment_type' => 'salaried',
            'employer_name' => 'Acme Corp',
            'monthly_income' => 75000,
        ]);
        $this->assertCount(1, $customer->fresh()->employment);

        // Add Bank Account
        $this->actingAs($this->user)->post(route('customers.bank-accounts.store', $customer), [
            'bank_name' => 'HDFC Bank',
            'account_number' => '1234567890',
            'account_holder_name' => 'David Sub',
            'account_type' => 'savings',
        ]);
        $this->assertCount(1, $customer->fresh()->bankAccounts);

        // Add Nominee
        $this->actingAs($this->user)->post(route('customers.nominees.store', $customer), [
            'name' => 'Sarah Sub',
            'relationship' => 'Spouse',
            'share_percent' => 100,
        ]);
        $this->assertCount(1, $customer->fresh()->nominees);

        // Add Reference
        $this->actingAs($this->user)->post(route('customers.references.store', $customer), [
            'name' => 'John Friend',
            'phone' => '9200000000',
            'relationship' => 'Friend',
        ]);
        $this->assertCount(1, $customer->fresh()->references);
    }
}
