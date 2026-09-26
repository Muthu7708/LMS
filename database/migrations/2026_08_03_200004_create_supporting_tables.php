<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // -------------------------------------------------------
        // CHART OF ACCOUNTS
        // -------------------------------------------------------
        Schema::create('chart_of_accounts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('parent_id')->nullable()->constrained('chart_of_accounts')->nullOnDelete();
            $table->string('code', 20)->unique();
            $table->string('name', 200);
            $table->enum('account_type', ['asset', 'liability', 'equity', 'income', 'expense']);
            $table->enum('account_sub_type', [
                'cash', 'bank', 'receivable', 'payable', 'loan_portfolio',
                'fixed_asset', 'current_asset', 'current_liability', 'long_term_liability',
                'capital', 'retained_earnings', 'interest_income', 'fee_income',
                'penalty_income', 'interest_expense', 'operating_expense', 'other'
            ])->nullable();
            $table->enum('normal_balance', ['debit', 'credit'])->default('debit');
            $table->boolean('is_system_account')->default(false)->comment('Cannot be deleted');
            $table->boolean('is_bank_account')->default(false);
            $table->string('bank_name', 150)->nullable();
            $table->string('bank_account_no', 30)->nullable();
            $table->boolean('allow_manual_entry')->default(true);
            $table->boolean('is_active')->default(true);
            $table->integer('sort_order')->default(0);
            $table->text('description')->nullable();
            $table->timestamps();

            $table->index(['company_id', 'account_type']);
        });

        // -------------------------------------------------------
        // JOURNAL ENTRIES (Double-entry)
        // -------------------------------------------------------
        Schema::create('journal_entries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('branch_id')->constrained()->restrictOnDelete();
            $table->foreignId('created_by')->constrained('users')->restrictOnDelete();
            $table->string('journal_no', 30)->unique();
            $table->date('entry_date');
            $table->string('reference_type', 50)->nullable()->comment('loan_payment, loan_disbursement, penalty, etc.');
            $table->unsignedBigInteger('reference_id')->nullable()->comment('ID of the source transaction');
            $table->text('narration');
            $table->decimal('total_debit', 15, 2)->default(0);
            $table->decimal('total_credit', 15, 2)->default(0);
            $table->enum('status', ['draft', 'posted', 'reversed'])->default('draft');
            $table->foreignId('posted_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('posted_at')->nullable();
            $table->foreignId('reversed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('reversed_at')->nullable();
            $table->foreignId('reversal_of')->nullable()->constrained('journal_entries')->nullOnDelete();
            $table->timestamps();

            $table->index(['company_id', 'entry_date']);
            $table->index(['reference_type', 'reference_id']);
        });

        // -------------------------------------------------------
        // JOURNAL ENTRY LINES (Debit/Credit lines)
        // -------------------------------------------------------
        Schema::create('journal_entry_lines', function (Blueprint $table) {
            $table->id();
            $table->foreignId('journal_entry_id')->constrained()->cascadeOnDelete();
            $table->foreignId('account_id')->constrained('chart_of_accounts')->restrictOnDelete();
            $table->enum('type', ['debit', 'credit']);
            $table->decimal('amount', 15, 2);
            $table->text('description')->nullable();
            $table->timestamps();

            $table->index(['journal_entry_id']);
            $table->index(['account_id']);
        });

        // -------------------------------------------------------
        // DOCUMENT MANAGEMENT
        // -------------------------------------------------------
        Schema::create('documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->morphs('documentable'); // customer, loan, etc. — polymorphic
            $table->foreignId('uploaded_by')->constrained('users')->restrictOnDelete();
            $table->string('document_type', 50);
            $table->string('document_category', 50)->nullable();
            $table->string('title', 200);
            $table->text('description')->nullable();
            $table->enum('status', ['draft', 'pending_review', 'approved', 'rejected', 'expired', 'renewed'])->default('pending_review');
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('reviewed_at')->nullable();
            $table->text('review_remarks')->nullable();
            $table->date('valid_from')->nullable();
            $table->date('valid_until')->nullable();
            $table->boolean('is_verified')->default(false);
            $table->timestamps();
            $table->softDeletes();

            // Note: morphs() already creates the index on documentable_type + documentable_id
        });

        // -------------------------------------------------------
        // DOCUMENT VERSIONS (Versioned uploads)
        // -------------------------------------------------------
        Schema::create('document_versions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('document_id')->constrained()->cascadeOnDelete();
            $table->foreignId('uploaded_by')->constrained('users')->restrictOnDelete();
            $table->integer('version_number')->default(1);
            $table->string('file_path');
            $table->string('file_name', 255);
            $table->string('file_type', 50)->comment('pdf, jpg, png, etc.');
            $table->unsignedBigInteger('file_size')->comment('bytes');
            $table->string('file_hash', 64)->nullable()->comment('SHA256 for integrity verification');
            $table->boolean('is_current')->default(true);
            $table->text('change_notes')->nullable();
            $table->timestamps();

            $table->index(['document_id', 'is_current']);
        });

        // -------------------------------------------------------
        // NOTIFICATIONS LOG
        // -------------------------------------------------------
        Schema::create('notification_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->string('event', 100)->comment('loan_approved, emi_due, penalty_applied, etc.');
            $table->string('channel', 30)->comment('email, sms, whatsapp');
            $table->string('recipient_type', 50)->comment('customer, user, guarantor');
            $table->unsignedBigInteger('recipient_id');
            $table->string('recipient_name', 150)->nullable();
            $table->string('recipient_contact', 100)->comment('email address or phone number');
            $table->string('subject', 300)->nullable();
            $table->text('body')->nullable();
            $table->enum('status', ['queued', 'sent', 'delivered', 'failed', 'bounced'])->default('queued');
            $table->text('failure_reason')->nullable();
            $table->timestamp('sent_at')->nullable();
            $table->string('reference_type', 50)->nullable();
            $table->unsignedBigInteger('reference_id')->nullable();
            $table->timestamps();

            $table->index(['company_id', 'event']);
            $table->index(['recipient_type', 'recipient_id']);
            $table->index('status');
        });

        // -------------------------------------------------------
        // AUDIT LOGS (immutable)
        // -------------------------------------------------------
        Schema::create('audit_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('user_name', 150)->nullable();
            $table->string('user_role', 50)->nullable();
            $table->string('model_type', 100)->comment('App\Models\Loan, etc.');
            $table->unsignedBigInteger('model_id');
            $table->string('event', 50)->comment('created, updated, deleted, status_changed, etc.');
            $table->json('old_values')->nullable();
            $table->json('new_values')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->string('user_agent')->nullable();
            $table->text('description')->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->index(['model_type', 'model_id']);
            $table->index(['company_id', 'created_at']);
            $table->index('user_id');
        });

        // -------------------------------------------------------
        // SETTINGS (Company-level key-value store)
        // -------------------------------------------------------
        Schema::create('settings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->string('group', 100)->default('general')->comment('general, loan, accounting, notification, etc.');
            $table->string('key', 100);
            $table->text('value')->nullable();
            $table->string('type', 30)->default('string')->comment('string, integer, boolean, json');
            $table->string('label', 200)->nullable();
            $table->text('description')->nullable();
            $table->boolean('is_public')->default(false);
            $table->timestamps();

            $table->unique(['company_id', 'group', 'key']);
            $table->index(['company_id', 'group']);
        });

        // -------------------------------------------------------
        // RECEIPT REPRINTS LOG
        // -------------------------------------------------------
        Schema::create('receipt_reprints', function (Blueprint $table) {
            $table->id();
            $table->foreignId('payment_id')->constrained('loan_payments')->cascadeOnDelete();
            $table->foreignId('reprinted_by')->constrained('users')->restrictOnDelete();
            $table->string('receipt_no', 30);
            $table->string('reason', 200)->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->timestamp('reprinted_at')->useCurrent();
        });

        // -------------------------------------------------------
        // LOAN RENEWAL HISTORY
        // -------------------------------------------------------
        Schema::create('loan_renewals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('original_loan_id')->constrained('loans')->restrictOnDelete();
            $table->foreignId('new_loan_id')->constrained('loans')->restrictOnDelete();
            $table->foreignId('processed_by')->constrained('users')->restrictOnDelete();
            $table->decimal('old_outstanding', 15, 2);
            $table->decimal('new_loan_amount', 15, 2);
            $table->decimal('top_up_amount', 15, 2)->default(0)->comment('Additional amount in renewal');
            $table->date('renewal_date');
            $table->text('reason')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('loan_renewals');
        Schema::dropIfExists('receipt_reprints');
        Schema::dropIfExists('settings');
        Schema::dropIfExists('audit_logs');
        Schema::dropIfExists('notification_logs');
        Schema::dropIfExists('document_versions');
        Schema::dropIfExists('documents');
        Schema::dropIfExists('journal_entry_lines');
        Schema::dropIfExists('journal_entries');
        Schema::dropIfExists('chart_of_accounts');
    }
};
