<?php

namespace Tests\Feature;

use App\Models\Branch;
use App\Models\Company;
use App\Models\Customer;
use App\Models\Document;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class DocumentManagementE2ETest extends TestCase
{
    use DatabaseTransactions;

    protected Company $company;
    protected Branch $branch;
    protected User $user;
    protected Customer $customer;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('public');

        $this->company = Company::create([
            'name' => 'Doc Co',
            'code' => 'DOCCO',
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
            'name' => 'Doc Officer',
            'email' => 'docofficer_' . time() . rand(100, 999) . '@test.com',
            'password' => bcrypt('password'),
            'is_active' => true,
        ]);

        $role = Role::firstOrCreate(['name' => 'super_admin', 'guard_name' => 'web']);
        $this->user->assignRole($role);

        $permissions = [
            'document.view', 'document.upload', 'document.approve'
        ];
        foreach ($permissions as $perm) {
            Permission::firstOrCreate(['name' => $perm, 'guard_name' => 'web']);
            $role->givePermissionTo($perm);
        }

        $this->customer = Customer::create([
            'company_id' => $this->company->id,
            'branch_id' => $this->branch->id,
            'created_by' => $this->user->id,
            'customer_no' => 'CUST-' . rand(10000, 99999),
            'first_name' => 'Doc',
            'last_name' => 'Owner',
            'mobile' => '88' . rand(10000000, 99999999),
            'status' => 'active',
        ]);
    }

    public function test_can_list_documents(): void
    {
        $response = $this->actingAs($this->user)->get(route('documents.index'));
        $response->assertStatus(200);
    }

    public function test_can_upload_document(): void
    {
        $file = UploadedFile::fake()->create('id_proof.pdf', 500, 'application/pdf');

        $response = $this->actingAs($this->user)->post(route('documents.upload'), [
            'documentable_type' => Customer::class,
            'documentable_id' => $this->customer->id,
            'title' => 'Aadhaar Card Copy',
            'document_type' => 'identity_proof',
            'file' => $file,
        ]);

        $response->assertRedirect();
        $doc = Document::where('documentable_id', $this->customer->id)->first();
        $this->assertNotNull($doc);
        $this->assertEquals('Aadhaar Card Copy', $doc->title);
        $this->assertCount(1, $doc->versions);
    }

    public function test_can_add_document_version(): void
    {
        $file1 = UploadedFile::fake()->create('v1.pdf', 500, 'application/pdf');
        $this->actingAs($this->user)->post(route('documents.upload'), [
            'documentable_type' => Customer::class,
            'documentable_id' => $this->customer->id,
            'title' => 'Bank Statement',
            'document_type' => 'income_proof',
            'file' => $file1,
        ]);

        $doc = Document::where('title', 'Bank Statement')->first();

        // Upload Version 2
        $file2 = UploadedFile::fake()->create('v2.pdf', 600, 'application/pdf');
        $response = $this->actingAs($this->user)->post(route('documents.version', $doc), [
            'file' => $file2,
            'change_notes' => 'Updated last 6 months statement',
        ]);

        $response->assertRedirect();
        $this->assertCount(2, $doc->fresh()->versions);
        $this->assertEquals(2, $doc->fresh()->currentVersion->version_number);
    }

    public function test_can_approve_document(): void
    {
        $file = UploadedFile::fake()->create('income.pdf', 500, 'application/pdf');
        $this->actingAs($this->user)->post(route('documents.upload'), [
            'documentable_type' => Customer::class,
            'documentable_id' => $this->customer->id,
            'title' => 'Salary Slip',
            'document_type' => 'income_proof',
            'file' => $file,
        ]);

        $doc = Document::where('title', 'Salary Slip')->first();

        $response = $this->actingAs($this->user)->post(route('documents.approve', $doc), [
            'remarks' => 'Verified against salary bank credits',
        ]);

        $response->assertRedirect();
        $this->assertEquals('approved', $doc->fresh()->status);
        $this->assertTrue($doc->fresh()->is_verified);
    }

    public function test_can_download_document_version(): void
    {
        $file = UploadedFile::fake()->create('testdoc.pdf', 300, 'application/pdf');
        $this->actingAs($this->user)->post(route('documents.upload'), [
            'documentable_type' => Customer::class,
            'documentable_id' => $this->customer->id,
            'title' => 'Download Test Doc',
            'document_type' => 'other',
            'file' => $file,
        ]);

        $doc = Document::where('title', 'Download Test Doc')->first();
        $version = $doc->currentVersion;

        $response = $this->actingAs($this->user)->get(route('documents.download', $version));
        $response->assertStatus(200);
    }
}
