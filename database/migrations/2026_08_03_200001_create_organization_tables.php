<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // -------------------------------------------------------
        // 1. COMPANY
        // -------------------------------------------------------
        Schema::create('companies', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('code', 20)->unique();
            $table->string('logo')->nullable();
            $table->string('address')->nullable();
            $table->string('city', 100)->nullable();
            $table->string('state', 100)->nullable();
            $table->string('country', 100)->default('India');
            $table->string('pin_code', 10)->nullable();
            $table->string('phone', 20)->nullable();
            $table->string('email')->nullable();
            $table->string('website')->nullable();
            $table->string('pan', 20)->nullable();
            $table->string('gstin', 20)->nullable();
            $table->string('cin', 25)->nullable();
            $table->string('rbi_license_no', 50)->nullable();
            $table->string('currency', 3)->default('INR');
            $table->string('currency_symbol', 5)->default('₹');
            $table->string('fiscal_year_start', 5)->default('04-01'); // MM-DD
            $table->boolean('is_active')->default(true);
            $table->json('settings')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        // -------------------------------------------------------
        // 2. BRANCHES
        // -------------------------------------------------------
        Schema::create('branches', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('code', 20)->unique();
            $table->string('address')->nullable();
            $table->string('city', 100)->nullable();
            $table->string('state', 100)->nullable();
            $table->string('pin_code', 10)->nullable();
            $table->string('phone', 20)->nullable();
            $table->string('email')->nullable();
            $table->decimal('loan_limit', 15, 2)->nullable()->comment('Max loan amount this branch can approve');
            $table->boolean('is_head_office')->default(false);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });

        // -------------------------------------------------------
        // 3. USERS (extend default users table)
        // -------------------------------------------------------
        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('company_id')->nullable()->after('id')->constrained()->nullOnDelete();
            $table->foreignId('branch_id')->nullable()->after('company_id')->constrained()->nullOnDelete();
            $table->string('employee_id', 50)->nullable()->after('email');
            $table->string('phone', 20)->nullable()->after('employee_id');
            $table->string('avatar')->nullable();
            $table->date('date_of_joining')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamp('last_login_at')->nullable();
            $table->string('last_login_ip', 45)->nullable();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropSoftDeletes();
            $table->dropColumn(['company_id', 'branch_id', 'employee_id', 'phone', 'avatar', 'date_of_joining', 'is_active', 'last_login_at', 'last_login_ip']);
        });
        Schema::dropIfExists('branches');
        Schema::dropIfExists('companies');
    }
};
