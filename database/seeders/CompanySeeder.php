<?php

namespace Database\Seeders;

use App\Models\Branch;
use App\Models\Company;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class CompanySeeder extends Seeder
{
    public function run(): void
    {
        // Create the default company
        $company = Company::firstOrCreate(
            ['code' => 'MAIN'],
            [
                'name'              => 'Finance ERP Demo Company',
                'address'           => '123 Finance Street',
                'city'              => 'Mumbai',
                'state'             => 'Maharashtra',
                'country'           => 'India',
                'pin_code'          => '400001',
                'phone'             => '022-12345678',
                'email'             => 'admin@financeerp.local',
                'currency'          => 'INR',
                'currency_symbol'   => '₹',
                'fiscal_year_start' => '04-01',
                'is_active'         => true,
            ]
        );

        // Create Head Office branch
        $headOffice = Branch::firstOrCreate(
            ['code' => 'HO'],
            [
                'company_id'    => $company->id,
                'name'          => 'Head Office',
                'address'       => '123 Finance Street',
                'city'          => 'Mumbai',
                'state'         => 'Maharashtra',
                'pin_code'      => '400001',
                'phone'         => '022-12345678',
                'email'         => 'ho@financeerp.local',
                'is_head_office'=> true,
                'is_active'     => true,
            ]
        );

        // Create Branch A
        $branchA = Branch::firstOrCreate(
            ['code' => 'BRA'],
            [
                'company_id'    => $company->id,
                'name'          => 'Branch A - Delhi',
                'address'       => '45 Connaught Place',
                'city'          => 'Delhi',
                'state'         => 'Delhi',
                'pin_code'      => '110001',
                'phone'         => '011-12345678',
                'email'         => 'branch-a@financeerp.local',
                'is_head_office'=> false,
                'is_active'     => true,
            ]
        );

        // Create Branch B
        $branchB = Branch::firstOrCreate(
            ['code' => 'BRB'],
            [
                'company_id'    => $company->id,
                'name'          => 'Branch B - Bangalore',
                'address'       => '78 MG Road',
                'city'          => 'Bangalore',
                'state'         => 'Karnataka',
                'pin_code'      => '560001',
                'phone'         => '080-12345678',
                'email'         => 'branch-b@financeerp.local',
                'is_head_office'=> false,
                'is_active'     => true,
            ]
        );

        // Update Super Admin user to be linked to company + head office
        User::where('email', 'superadmin@financeerp.local')
            ->update(['company_id' => $company->id, 'branch_id' => $headOffice->id]);

        // Create a Company Admin user
        $companyAdmin = User::firstOrCreate(
            ['email' => 'admin@financeerp.local'],
            [
                'name'       => 'Company Administrator',
                'password'   => Hash::make('Admin@12345'),
                'company_id' => $company->id,
                'branch_id'  => $headOffice->id,
                'phone'      => '9888888888',
                'is_active'  => true,
            ]
        );
        $companyAdmin->assignRole('company_admin');

        // Create a Branch Manager for Head Office
        $branchMgr = User::firstOrCreate(
            ['email' => 'manager@financeerp.local'],
            [
                'name'       => 'Branch Manager (HO)',
                'password'   => Hash::make('Manager@12345'),
                'company_id' => $company->id,
                'branch_id'  => $headOffice->id,
                'phone'      => '9777777777',
                'is_active'  => true,
            ]
        );
        $branchMgr->assignRole('branch_manager');

        // Create a Loan Officer
        $loanOfficer = User::firstOrCreate(
            ['email' => 'officer@financeerp.local'],
            [
                'name'       => 'Loan Officer',
                'password'   => Hash::make('Officer@12345'),
                'company_id' => $company->id,
                'branch_id'  => $headOffice->id,
                'phone'      => '9666666666',
                'is_active'  => true,
            ]
        );
        $loanOfficer->assignRole('loan_officer');

        // Create a Cashier
        $cashier = User::firstOrCreate(
            ['email' => 'cashier@financeerp.local'],
            [
                'name'       => 'Cashier',
                'password'   => Hash::make('Cashier@12345'),
                'company_id' => $company->id,
                'branch_id'  => $headOffice->id,
                'phone'      => '9555555555',
                'is_active'  => true,
            ]
        );
        $cashier->assignRole('cashier');

        $this->command->info('✅ Company, Branches, and Demo Users created.');
        $this->command->table(
            ['Role', 'Email', 'Password'],
            [
                ['Super Admin',    'superadmin@financeerp.local', 'Admin@12345'],
                ['Company Admin',  'admin@financeerp.local',      'Admin@12345'],
                ['Branch Manager', 'manager@financeerp.local',    'Manager@12345'],
                ['Loan Officer',   'officer@financeerp.local',    'Officer@12345'],
                ['Cashier',        'cashier@financeerp.local',    'Cashier@12345'],
            ]
        );
    }
}
