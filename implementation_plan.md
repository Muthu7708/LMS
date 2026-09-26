# Implementation Plan - Comprehensive E2E Test Suite for LMS

This document outlines the plan for building a comprehensive End-to-End (E2E) feature test suite covering all functional modules and user workflows in the Loan Management System (LMS).

## Target Modules & Test Suites

The test suite will be structured as Laravel Feature tests using `DatabaseTransactions` to ensure clean, isolated execution against test database environments.

---

### Proposed Test Suites

#### 1. Authentication & Profile Test
**File:** [NEW] [AuthE2ETest.php](file:///e:/xampp/htdocs/LMS/tests/Feature/AuthE2ETest.php)
- **Flows Covered:**
  - Login form rendering & validation.
  - Authentication with valid user credentials.
  - Failed login with invalid credentials.
  - User logout and session invalidation.
  - Viewing and updating user profile details.

---

#### 2. Branch & Organization Test
**File:** [NEW] [BranchE2ETest.php](file:///e:/xampp/htdocs/LMS/tests/Feature/BranchE2ETest.php)
- **Flows Covered:**
  - Listing all organization branches with search & filtering.
  - Creating a new branch.
  - Viewing branch details.
  - Editing branch details.
  - Deleting/deactivating a branch.
  - Verifying permission middleware (`branch.view`, `branch.create`, `branch.edit`, `branch.delete`).

---

#### 3. User & Role Management Test
**File:** [NEW] [UserManagementE2ETest.php](file:///e:/xampp/htdocs/LMS/tests/Feature/UserManagementE2ETest.php)
- **Flows Covered:**
  - Creating new system users with assigned roles (Super Admin, Loan Officer, Manager).
  - Editing user profile and reassigning roles/branches.
  - Toggling user active/inactive status (`toggleActive`).
  - Authorization check ensuring inactive users cannot perform restricted actions.

---

#### 4. Customer Onboarding & Management Test
**File:** [NEW] [CustomerE2ETest.php](file:///e:/xampp/htdocs/LMS/tests/Feature/CustomerE2ETest.php)
- **Flows Covered:**
  - Customer registration & profile creation.
  - Updating customer details.
  - Adding sub-resources: Addresses, KYC documents, Employment details, Bank accounts, Nominees, and Personal references.
  - Customer blacklisting with reason & unblacklisting flow.

---

#### 5. Loan Lifecycle E2E Test
**File:** [NEW] [LoanLifecycleE2ETest.php](file:///e:/xampp/htdocs/LMS/tests/Feature/LoanLifecycleE2ETest.php)
- **Flows Covered:**
  - Draft application creation (Personal, Flat/Reducing rate).
  - Adding collateral assets and guarantors to loan application.
  - Loan application submission (`draft` -> `submitted`).
  - Field verification (`submitted` -> `verified`).
  - Loan approval with custom amount/interest/tenure (`verified` -> `approved`).
  - Loan rejection flow (`submitted`/`verified` -> `rejected`).
  - Disbursement execution (`approved` -> `active`), verifying automatic generation of EMI schedules.

---

#### 6. EMI Simulation, Payment Collection & Reversals Test
**File:** [NEW] [LoanPaymentE2ETest.php](file:///e:/xampp/htdocs/LMS/tests/Feature/LoanPaymentE2ETest.php)
- **Flows Covered:**
  - Simulating EMI repayment breakdown via `/loans/simulate`.
  - Collecting EMI payment (full payment, partial payment, and over-payment).
  - Verification of EMI status updates (`paid`, `partial`) and outstanding principal/interest reduction.
  - Receipt generation & reprinting.
  - Payment reversal flow (`payments/{payment}/reverse`) and resetting EMI schedule state.

---

#### 7. Waivers, Restructuring & Foreclosure Settlement Test
**File:** [NEW] [LoanSettlementAndForeclosureE2ETest.php](file:///e:/xampp/htdocs/LMS/tests/Feature/LoanSettlementAndForeclosureE2ETest.php)
- **Flows Covered:**
  - Penalty & fee waiver request and approval flow.
  - Loan restructuring (tenure/rate adjustment & EMI schedule recalculation).
  - Loan foreclosure calculation (`calculateForeclosure`).
  - Submitting foreclosure settlement payment, marking loan status as `closed`, outstanding principal as `0`, and all pending EMIs as `paid`.

---

#### 8. Accounting & Journal Entry Test
**File:** [NEW] [AccountingE2ETest.php](file:///e:/xampp/htdocs/LMS/tests/Feature/AccountingE2ETest.php)
- **Flows Covered:**
  - Managing Chart of Accounts (COA).
  - Creating manual journal vouchers.
  - Posting journal entries to ledger accounts.
  - Viewing ledger, trial balance, profit & loss, and balance sheet reports.

---

#### 9. Document Management Test
**File:** [NEW] [DocumentManagementE2ETest.php](file:///e:/xampp/htdocs/LMS/tests/Feature/DocumentManagementE2ETest.php)
- **Flows Covered:**
  - Uploading loan/customer documents.
  - Version control for uploaded documents (`addVersion`).
  - Document verification and approval (`approve`).
  - Secure document download stream.

---

#### 10. Reports & Data Exports Test
**File:** [NEW] [ReportsE2ETest.php](file:///e:/xampp/htdocs/LMS/tests/Feature/ReportsE2ETest.php)
- **Flows Covered:**
  - Accessing Disbursement, Collection, Overdue, Portfolio, and NPA reports.
  - Generating customer account statements.
  - PDF & Excel export routes verification.

---

## Verification Plan

### Automated Testing
Run the complete test suite using PHPUnit:
```bash
php artisan test
```
Or run individual feature test suites:
```bash
php artisan test --filter=AuthE2ETest
php artisan test --filter=BranchE2ETest
php artisan test --filter=UserManagementE2ETest
php artisan test --filter=CustomerE2ETest
php artisan test --filter=LoanLifecycleE2ETest
php artisan test --filter=LoanPaymentE2ETest
php artisan test --filter=LoanSettlementAndForeclosureE2ETest
php artisan test --filter=AccountingE2ETest
php artisan test --filter=DocumentManagementE2ETest
php artisan test --filter=ReportsE2ETest
```

### Verification Criteria
- All tests execute with 0 failures and 0 errors.
- Every major business workflow (Application -> Approval -> Disbursement -> Payment -> Foreclosure / Restructure / Accounting / Reporting) is validated automatically.
