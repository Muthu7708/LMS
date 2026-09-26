<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // -------------------------------------------------------
        // LOAN APPLICATIONS — Master loan record
        // -------------------------------------------------------
        Schema::create('loans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('branch_id')->constrained()->cascadeOnDelete();
            $table->foreignId('customer_id')->constrained()->restrictOnDelete();
            $table->foreignId('loan_officer_id')->constrained('users')->restrictOnDelete();
            $table->string('loan_no', 30)->unique()->comment('e.g. LN-2024-00001');
            $table->string('loan_type', 30)->comment('personal, business, vehicle, gold, etc.');
            $table->string('purpose', 200)->nullable();
            // Financial Terms
            $table->decimal('applied_amount', 15, 2);
            $table->decimal('approved_amount', 15, 2)->nullable();
            $table->decimal('disbursed_amount', 15, 2)->nullable();
            $table->integer('tenure_months');
            $table->decimal('interest_rate', 6, 4)->comment('Annual rate, e.g. 12.50 = 12.5% per annum');
            $table->string('interest_method', 30)->default('reducing')->comment('flat, reducing, reducing_daily');
            $table->string('payment_frequency', 20)->default('monthly');
            $table->decimal('processing_fee', 12, 2)->default(0);
            $table->decimal('processing_fee_gst', 12, 2)->default(0);
            $table->decimal('insurance_amount', 12, 2)->default(0);
            $table->decimal('other_charges', 12, 2)->default(0);
            $table->integer('grace_period_months')->default(0);
            $table->integer('moratorium_months')->default(0)->comment('Interest-only period after disbursement');
            $table->string('prepayment_option', 30)->default('reduce_tenure');
            // Dates
            $table->date('application_date');
            $table->date('approved_date')->nullable();
            $table->date('disbursement_date')->nullable();
            $table->date('first_emi_date')->nullable();
            $table->date('last_emi_date')->nullable();
            $table->date('maturity_date')->nullable();
            $table->date('closed_date')->nullable();
            // EMI Details (calculated on disbursement)
            $table->decimal('emi_amount', 12, 2)->nullable();
            $table->integer('total_emis')->nullable();
            $table->decimal('total_interest_payable', 15, 2)->nullable();
            $table->decimal('total_amount_payable', 15, 2)->nullable();
            // Outstanding (updated on every payment)
            $table->decimal('outstanding_principal', 15, 2)->nullable();
            $table->decimal('outstanding_interest', 15, 2)->nullable();
            $table->decimal('outstanding_penalty', 15, 2)->default(0);
            $table->decimal('total_paid', 15, 2)->default(0);
            $table->decimal('total_paid_principal', 15, 2)->default(0);
            $table->decimal('total_paid_interest', 15, 2)->default(0);
            $table->decimal('total_paid_penalty', 15, 2)->default(0);
            $table->integer('emis_paid')->default(0);
            $table->integer('emis_overdue')->default(0);
            // Status & Workflow
            $table->string('status', 30)->default('draft');
            $table->text('rejection_reason')->nullable();
            $table->boolean('is_restructured')->default(false);
            $table->boolean('is_npa')->default(false);
            $table->date('npa_date')->nullable();
            $table->enum('npa_category', ['sub_standard', 'doubtful', 'loss'])->nullable();
            $table->boolean('is_renewed')->default(false);
            $table->foreignId('renewed_from_loan_id')->nullable()->constrained('loans')->nullOnDelete();
            $table->text('internal_notes')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['company_id', 'branch_id', 'status']);
            $table->index(['customer_id', 'status']);
            $table->index('loan_no');
            $table->index('status');
            $table->index('disbursement_date');
        });

        // -------------------------------------------------------
        // LOAN VERIFICATIONS
        // -------------------------------------------------------
        Schema::create('loan_verifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('loan_id')->constrained()->cascadeOnDelete();
            $table->foreignId('verified_by')->constrained('users')->restrictOnDelete();
            $table->enum('verification_type', ['field', 'telephonic', 'document', 'residence', 'office'])->default('document');
            $table->enum('status', ['pending', 'in_progress', 'completed', 'failed'])->default('pending');
            $table->text('remarks')->nullable();
            $table->json('checklist')->nullable()->comment('JSON checklist of verification items');
            $table->string('gps_coordinates', 50)->nullable();
            $table->timestamp('visited_at')->nullable();
            $table->timestamps();
        });

        // -------------------------------------------------------
        // LOAN APPROVALS (Multi-level — one record per level)
        // -------------------------------------------------------
        Schema::create('loan_approvals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('loan_id')->constrained()->cascadeOnDelete();
            $table->foreignId('approved_by')->constrained('users')->restrictOnDelete();
            $table->integer('approval_level')->default(1)->comment('1=Loan Officer, 2=Branch Manager, 3=Company Admin');
            $table->string('approver_role', 50);
            $table->enum('action', ['recommended', 'approved', 'rejected', 'deferred', 'returned'])->default('approved');
            $table->decimal('approved_amount', 15, 2)->nullable();
            $table->decimal('approved_rate', 6, 4)->nullable();
            $table->integer('approved_tenure')->nullable();
            $table->text('conditions')->nullable();
            $table->text('remarks')->nullable();
            $table->timestamp('actioned_at');
            $table->timestamps();
        });

        // -------------------------------------------------------
        // LOAN COLLATERALS
        // -------------------------------------------------------
        Schema::create('loan_collaterals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('loan_id')->constrained()->cascadeOnDelete();
            $table->enum('collateral_type', ['property', 'vehicle', 'gold', 'fd', 'shares', 'insurance', 'machinery', 'other']);
            $table->text('description');
            $table->string('owner_name', 150)->nullable();
            $table->decimal('estimated_value', 15, 2)->nullable();
            $table->decimal('market_value', 15, 2)->nullable();
            $table->string('valuation_by', 150)->nullable();
            $table->date('valuation_date')->nullable();
            $table->string('insurance_policy_no', 50)->nullable();
            $table->date('insurance_expiry')->nullable();
            $table->text('address')->nullable()->comment('For property collateral');
            $table->string('registration_no', 50)->nullable()->comment('For vehicle collateral');
            $table->decimal('gold_weight_grams', 10, 4)->nullable();
            $table->decimal('gold_purity_karats', 4, 2)->nullable();
            $table->boolean('is_released')->default(false);
            $table->date('released_date')->nullable();
            $table->timestamps();
        });

        // -------------------------------------------------------
        // LOAN GUARANTORS
        // -------------------------------------------------------
        Schema::create('loan_guarantors', function (Blueprint $table) {
            $table->id();
            $table->foreignId('loan_id')->constrained()->cascadeOnDelete();
            $table->foreignId('customer_id')->nullable()->constrained()->nullOnDelete()
                ->comment('If guarantor is also a customer');
            $table->string('name', 150);
            $table->string('relationship', 100)->nullable();
            $table->string('mobile', 15);
            $table->string('email')->nullable();
            $table->string('pan', 10)->nullable();
            $table->string('aadhaar', 12)->nullable();
            $table->decimal('monthly_income', 12, 2)->nullable();
            $table->text('address')->nullable();
            $table->string('employment_type', 50)->nullable();
            $table->string('employer_name', 200)->nullable();
            $table->boolean('has_signed')->default(false);
            $table->date('signed_date')->nullable();
            $table->timestamps();
        });

        // -------------------------------------------------------
        // EMI SCHEDULES
        // -------------------------------------------------------
        Schema::create('loan_emi_schedules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('loan_id')->constrained()->cascadeOnDelete();
            $table->integer('emi_number');
            $table->date('due_date');
            $table->decimal('emi_amount', 12, 2);
            $table->decimal('principal_amount', 12, 2);
            $table->decimal('interest_amount', 12, 2);
            $table->decimal('opening_balance', 15, 2)->comment('Outstanding principal at start of period');
            $table->decimal('closing_balance', 15, 2)->comment('Outstanding principal after EMI');
            // Payment tracking
            $table->decimal('paid_amount', 12, 2)->default(0);
            $table->decimal('paid_principal', 12, 2)->default(0);
            $table->decimal('paid_interest', 12, 2)->default(0);
            $table->date('paid_date')->nullable();
            $table->decimal('waived_amount', 12, 2)->default(0);
            // Penalty
            $table->integer('days_overdue')->default(0);
            $table->decimal('penalty_amount', 12, 2)->default(0);
            $table->decimal('paid_penalty', 12, 2)->default(0);
            // Status
            $table->enum('status', ['pending', 'paid', 'partial', 'overdue', 'waived', 'restructured'])->default('pending');
            $table->boolean('is_moratorium')->default(false)->comment('Interest-only period');
            $table->boolean('is_grace')->default(false)->comment('Grace period - no EMI');
            $table->timestamps();

            $table->unique(['loan_id', 'emi_number']);
            $table->index(['loan_id', 'status']);
            $table->index(['due_date', 'status']);
        });

        // -------------------------------------------------------
        // LOAN PAYMENTS (actual money received)
        // -------------------------------------------------------
        Schema::create('loan_payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('loan_id')->constrained()->cascadeOnDelete();
            $table->foreignId('branch_id')->constrained()->restrictOnDelete();
            $table->foreignId('collected_by')->constrained('users')->restrictOnDelete();
            $table->string('receipt_no', 30)->unique();
            $table->date('payment_date');
            $table->decimal('amount', 12, 2)->comment('Total amount received');
            $table->decimal('principal_paid', 12, 2)->default(0);
            $table->decimal('interest_paid', 12, 2)->default(0);
            $table->decimal('penalty_paid', 12, 2)->default(0);
            $table->decimal('excess_amount', 12, 2)->default(0)->comment('Overpayment, goes to next EMI');
            $table->string('payment_mode', 30)->default('cash');
            $table->string('reference_no', 100)->nullable()->comment('Cheque/UTR/Transaction number');
            $table->string('bank_name', 100)->nullable()->comment('For cheque payments');
            $table->date('cheque_date')->nullable();
            $table->text('remarks')->nullable();
            $table->boolean('is_reversed')->default(false);
            $table->timestamp('reversed_at')->nullable();
            $table->foreignId('reversed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->text('reversal_reason')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['loan_id', 'payment_date']);
            $table->index('receipt_no');
        });

        // -------------------------------------------------------
        // LOAN PENALTIES (auto-generated or manual)
        // -------------------------------------------------------
        Schema::create('loan_penalties', function (Blueprint $table) {
            $table->id();
            $table->foreignId('loan_id')->constrained()->cascadeOnDelete();
            $table->foreignId('emi_schedule_id')->nullable()->constrained('loan_emi_schedules')->nullOnDelete();
            $table->date('penalty_date');
            $table->integer('overdue_days');
            $table->decimal('overdue_amount', 12, 2);
            $table->decimal('penalty_rate', 6, 4)->comment('% per day applied');
            $table->decimal('penalty_amount', 12, 2);
            $table->decimal('paid_amount', 12, 2)->default(0);
            $table->decimal('waived_amount', 12, 2)->default(0);
            $table->enum('status', ['outstanding', 'paid', 'waived', 'partial'])->default('outstanding');
            $table->timestamps();

            $table->index(['loan_id', 'status']);
        });

        // -------------------------------------------------------
        // LOAN WAIVERS
        // -------------------------------------------------------
        Schema::create('loan_waivers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('loan_id')->constrained()->cascadeOnDelete();
            $table->foreignId('requested_by')->constrained('users')->restrictOnDelete();
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->enum('waiver_type', ['interest', 'penalty', 'principal', 'other']);
            $table->decimal('requested_amount', 12, 2);
            $table->decimal('approved_amount', 12, 2)->nullable();
            $table->text('reason');
            $table->text('approver_remarks')->nullable();
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->timestamp('actioned_at')->nullable();
            $table->timestamps();
        });

        // -------------------------------------------------------
        // LOAN RESTRUCTURE HISTORY (immutable — never overwrite)
        // -------------------------------------------------------
        Schema::create('loan_restructure_history', function (Blueprint $table) {
            $table->id();
            $table->foreignId('loan_id')->constrained()->cascadeOnDelete();
            $table->foreignId('restructured_by')->constrained('users')->restrictOnDelete();
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->integer('restructure_sequence')->comment('1st restructure, 2nd, etc.');
            // Old Values (snapshot before restructure)
            $table->decimal('old_outstanding_principal', 15, 2);
            $table->decimal('old_interest_rate', 6, 4);
            $table->integer('old_remaining_tenure');
            $table->decimal('old_emi_amount', 12, 2);
            $table->decimal('old_penalty_outstanding', 12, 2)->default(0);
            // New Values
            $table->decimal('new_outstanding_principal', 15, 2);
            $table->decimal('new_interest_rate', 6, 4);
            $table->integer('new_tenure_months');
            $table->decimal('new_emi_amount', 12, 2);
            $table->date('new_first_emi_date');
            $table->date('new_maturity_date');
            $table->decimal('waived_penalty', 12, 2)->default(0);
            $table->decimal('capitalized_interest', 12, 2)->default(0);
            $table->text('reason');
            $table->text('remarks')->nullable();
            $table->timestamp('effective_date');
            $table->timestamps();
        });

        // -------------------------------------------------------
        // LOAN FORECLOSURE
        // -------------------------------------------------------
        Schema::create('loan_foreclosures', function (Blueprint $table) {
            $table->id();
            $table->foreignId('loan_id')->constrained()->cascadeOnDelete();
            $table->foreignId('requested_by')->constrained('users')->restrictOnDelete();
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->date('request_date');
            $table->date('foreclosure_date')->nullable();
            $table->decimal('outstanding_principal', 15, 2);
            $table->decimal('outstanding_interest', 15, 2);
            $table->decimal('outstanding_penalty', 15, 2)->default(0);
            $table->decimal('foreclosure_charges', 12, 2)->default(0);
            $table->decimal('total_foreclosure_amount', 15, 2);
            $table->decimal('amount_paid', 15, 2)->nullable();
            $table->string('payment_mode', 30)->nullable();
            $table->string('payment_reference', 100)->nullable();
            $table->enum('status', ['requested', 'approved', 'completed', 'cancelled'])->default('requested');
            $table->text('remarks')->nullable();
            $table->timestamps();
        });

        // -------------------------------------------------------
        // LOAN SETTLEMENT
        // -------------------------------------------------------
        Schema::create('loan_settlements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('loan_id')->constrained()->cascadeOnDelete();
            $table->foreignId('settled_by')->constrained('users')->restrictOnDelete();
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->decimal('outstanding_amount', 15, 2);
            $table->decimal('settlement_amount', 15, 2);
            $table->decimal('amount_waived', 15, 2)->default(0);
            $table->date('settlement_date');
            $table->string('payment_mode', 30)->nullable();
            $table->string('payment_reference', 100)->nullable();
            $table->text('reason');
            $table->text('remarks')->nullable();
            $table->enum('status', ['proposed', 'approved', 'completed', 'rejected'])->default('proposed');
            $table->timestamps();
        });

        // -------------------------------------------------------
        // LOAN DISBURSEMENTS
        // -------------------------------------------------------
        Schema::create('loan_disbursements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('loan_id')->constrained()->cascadeOnDelete();
            $table->foreignId('disbursed_by')->constrained('users')->restrictOnDelete();
            $table->string('disbursement_no', 30)->unique();
            $table->integer('tranche_number')->default(1)->comment('For phased disbursement');
            $table->decimal('amount', 15, 2);
            $table->string('mode', 30)->comment('cash, bank_transfer, cheque, upi, dd');
            $table->string('reference_no', 100)->nullable()->comment('UTR, Cheque No, etc.');
            $table->string('bank_name', 150)->nullable();
            $table->string('account_number', 30)->nullable()->comment('Beneficiary account');
            $table->date('disbursement_date');
            $table->text('remarks')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('loan_disbursements');
        Schema::dropIfExists('loan_settlements');
        Schema::dropIfExists('loan_foreclosures');
        Schema::dropIfExists('loan_restructure_history');
        Schema::dropIfExists('loan_waivers');
        Schema::dropIfExists('loan_penalties');
        Schema::dropIfExists('loan_payments');
        Schema::dropIfExists('loan_emi_schedules');
        Schema::dropIfExists('loan_guarantors');
        Schema::dropIfExists('loan_collaterals');
        Schema::dropIfExists('loan_approvals');
        Schema::dropIfExists('loan_verifications');
        Schema::dropIfExists('loans');
    }
};
