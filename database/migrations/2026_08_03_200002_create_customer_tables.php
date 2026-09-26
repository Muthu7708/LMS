<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // -------------------------------------------------------
        // CUSTOMERS — Core profile
        // -------------------------------------------------------
        Schema::create('customers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('branch_id')->constrained()->cascadeOnDelete();
            $table->foreignId('created_by')->constrained('users')->restrictOnDelete();
            $table->string('customer_no', 30)->unique()->comment('System-generated unique number, e.g. CUST-00001');
            // Personal Details
            $table->string('first_name', 100);
            $table->string('middle_name', 100)->nullable();
            $table->string('last_name', 100);
            $table->string('full_name', 300)->storedAs("first_name || ' ' || COALESCE(middle_name || ' ', '') || last_name");
            $table->enum('gender', ['male', 'female', 'other'])->nullable();
            $table->date('date_of_birth')->nullable();
            $table->string('father_name', 150)->nullable();
            $table->string('mother_name', 150)->nullable();
            $table->string('spouse_name', 150)->nullable();
            $table->enum('marital_status', ['single', 'married', 'divorced', 'widowed', 'other'])->nullable();
            $table->enum('religion', ['hindu', 'muslim', 'christian', 'sikh', 'jain', 'buddhist', 'other'])->nullable();
            $table->string('caste', 100)->nullable();
            $table->enum('education', ['primary', 'secondary', 'higher_secondary', 'graduate', 'post_graduate', 'phd', 'other'])->nullable();
            // Contact
            $table->string('mobile', 15);
            $table->string('alternate_mobile', 15)->nullable();
            $table->string('email')->nullable();
            $table->string('whatsapp', 15)->nullable();
            // Identifiers
            $table->string('pan', 10)->nullable()->unique();
            $table->string('aadhaar', 12)->nullable();
            $table->string('voter_id', 20)->nullable();
            $table->string('driving_licence', 20)->nullable();
            $table->string('passport_no', 20)->nullable();
            // Status
            $table->enum('status', ['active', 'inactive', 'blacklisted', 'deceased'])->default('active');
            $table->text('blacklist_reason')->nullable();
            $table->timestamp('blacklisted_at')->nullable();
            $table->foreignId('blacklisted_by')->nullable()->constrained('users')->nullOnDelete();
            // Credit Score
            $table->integer('credit_score')->nullable()->comment('Internal score 0-100');
            $table->enum('risk_category', ['low', 'medium', 'high', 'very_high'])->nullable();
            $table->timestamp('last_score_updated_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['company_id', 'branch_id']);
            $table->index('mobile');
            $table->index('status');
        });

        // -------------------------------------------------------
        // CUSTOMER ADDRESSES
        // -------------------------------------------------------
        Schema::create('customer_addresses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->constrained()->cascadeOnDelete();
            $table->enum('type', ['current', 'permanent', 'office', 'other'])->default('current');
            $table->text('address_line1');
            $table->text('address_line2')->nullable();
            $table->string('landmark', 200)->nullable();
            $table->string('city', 100);
            $table->string('district', 100)->nullable();
            $table->string('state', 100);
            $table->string('country', 100)->default('India');
            $table->string('pin_code', 10);
            $table->boolean('is_primary')->default(false);
            $table->integer('years_at_address')->nullable();
            $table->enum('ownership', ['owned', 'rented', 'parental', 'company_provided', 'other'])->nullable();
            $table->timestamps();
        });

        // -------------------------------------------------------
        // CUSTOMER CONTACTS (Additional contacts beyond mobile)
        // -------------------------------------------------------
        Schema::create('customer_contacts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->constrained()->cascadeOnDelete();
            $table->string('name', 150);
            $table->string('relationship', 100)->nullable();
            $table->string('phone', 15);
            $table->string('email')->nullable();
            $table->boolean('is_emergency')->default(false);
            $table->timestamps();
        });

        // -------------------------------------------------------
        // CUSTOMER KYC
        // -------------------------------------------------------
        Schema::create('customer_kyc', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->constrained()->cascadeOnDelete();
            $table->string('document_type', 50)->comment('aadhaar, pan, passport, etc.');
            $table->string('document_category', 50)->comment('identity, address, income, photo');
            $table->string('document_number', 100)->nullable();
            $table->date('issue_date')->nullable();
            $table->date('expiry_date')->nullable();
            $table->string('issuing_authority', 200)->nullable();
            $table->enum('status', ['pending', 'verified', 'rejected', 'expired'])->default('pending');
            $table->text('rejection_reason')->nullable();
            $table->foreignId('verified_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('verified_at')->nullable();
            $table->timestamps();

            $table->index(['customer_id', 'document_type']);
        });

        // -------------------------------------------------------
        // CUSTOMER EMPLOYMENT
        // -------------------------------------------------------
        Schema::create('customer_employment', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->constrained()->cascadeOnDelete();
            $table->string('employment_type', 50);
            $table->string('employer_name', 200)->nullable();
            $table->string('designation', 100)->nullable();
            $table->string('department', 100)->nullable();
            $table->date('joining_date')->nullable();
            $table->integer('years_of_experience')->nullable();
            $table->decimal('monthly_income', 12, 2)->nullable();
            $table->decimal('other_income', 12, 2)->nullable();
            $table->string('income_source', 200)->nullable();
            $table->text('office_address')->nullable();
            $table->string('office_phone', 15)->nullable();
            $table->boolean('is_current')->default(true);
            $table->timestamps();
        });

        // -------------------------------------------------------
        // CUSTOMER BANK ACCOUNTS
        // -------------------------------------------------------
        Schema::create('customer_bank_accounts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->constrained()->cascadeOnDelete();
            $table->string('bank_name', 150);
            $table->string('branch_name', 150)->nullable();
            $table->string('account_number', 30);
            $table->string('account_holder_name', 200);
            $table->enum('account_type', ['savings', 'current', 'od', 'cc', 'nre', 'nro'])->default('savings');
            $table->string('ifsc_code', 15)->nullable();
            $table->string('micr_code', 10)->nullable();
            $table->boolean('is_primary')->default(false);
            $table->boolean('nach_mandate')->default(false)->comment('NACH/ECS auto-debit mandate registered');
            $table->date('mandate_registered_date')->nullable();
            $table->date('mandate_expiry_date')->nullable();
            $table->timestamps();
        });

        // -------------------------------------------------------
        // CUSTOMER NOMINEES
        // -------------------------------------------------------
        Schema::create('customer_nominees', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->constrained()->cascadeOnDelete();
            $table->string('name', 150);
            $table->string('relationship', 100);
            $table->date('date_of_birth')->nullable();
            $table->string('phone', 15)->nullable();
            $table->text('address')->nullable();
            $table->decimal('share_percent', 5, 2)->default(100.00);
            $table->boolean('is_minor')->default(false);
            $table->string('guardian_name', 150)->nullable();
            $table->timestamps();
        });

        // -------------------------------------------------------
        // CUSTOMER REFERENCES
        // -------------------------------------------------------
        Schema::create('customer_references', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->constrained()->cascadeOnDelete();
            $table->string('name', 150);
            $table->string('relationship', 100)->nullable();
            $table->string('phone', 15);
            $table->string('email')->nullable();
            $table->text('address')->nullable();
            $table->string('occupation', 100)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('customer_references');
        Schema::dropIfExists('customer_nominees');
        Schema::dropIfExists('customer_bank_accounts');
        Schema::dropIfExists('customer_employment');
        Schema::dropIfExists('customer_kyc');
        Schema::dropIfExists('customer_contacts');
        Schema::dropIfExists('customer_addresses');
        Schema::dropIfExists('customers');
    }
};
