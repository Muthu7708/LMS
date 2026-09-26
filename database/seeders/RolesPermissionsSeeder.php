<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;

class RolesPermissionsSeeder extends Seeder
{
    protected array $allPermissions = [
        'company.view', 'company.create', 'company.edit', 'company.delete',
        'branch.view', 'branch.create', 'branch.edit', 'branch.delete',
        'user.view', 'user.create', 'user.edit', 'user.delete', 'user.impersonate',
        'role.view', 'role.create', 'role.edit', 'role.delete',
        'customer.view', 'customer.create', 'customer.edit', 'customer.delete',
        'customer.blacklist', 'customer.unblacklist', 'customer.export', 'customer.kyc.verify',
        'loan.view', 'loan.create', 'loan.edit', 'loan.delete', 'loan.export',
        'loan.submit', 'loan.verify', 'loan.approve', 'loan.reject',
        'loan.disburse', 'loan.close', 'loan.write_off',
        'loan.restructure', 'loan.renew', 'loan.foreclosure', 'loan.settlement',
        'payment.view', 'payment.collect', 'payment.reverse',
        'payment.receipt.print', 'payment.receipt.reprint',
        'penalty.view', 'penalty.apply',
        'waiver.view', 'waiver.request', 'waiver.approve',
        'accounting.view', 'accounting.journal.create', 'accounting.journal.post',
        'accounting.journal.reverse', 'accounting.coa.manage',
        'document.view', 'document.upload', 'document.delete', 'document.approve',
        'report.loan', 'report.collection', 'report.overdue', 'report.portfolio',
        'report.statement', 'report.accounting', 'report.audit',
        'notification.view', 'notification.send',
        'settings.view', 'settings.edit',
        'audit.view',
        'dashboard.company', 'dashboard.branch', 'dashboard.officer',
    ];

    public function run(): void
    {
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        // 1. Create all permissions
        foreach ($this->allPermissions as $perm) {
            Permission::firstOrCreate(['name' => $perm, 'guard_name' => 'web']);
        }

        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        // 2. Create roles with specific permission lists
        $this->createRole('super_admin', $this->allPermissions);

        $this->createRole('company_admin', array_filter($this->allPermissions, fn ($p) =>
            !in_array($p, ['user.impersonate', 'company.delete'])
        ));

        $this->createRole('branch_manager', [
            'user.view', 'user.create', 'user.edit',
            'customer.view', 'customer.create', 'customer.edit', 'customer.delete',
            'customer.blacklist', 'customer.unblacklist', 'customer.export', 'customer.kyc.verify',
            'loan.view', 'loan.create', 'loan.edit', 'loan.submit', 'loan.verify',
            'loan.approve', 'loan.reject', 'loan.disburse', 'loan.close', 'loan.export',
            'loan.restructure', 'loan.renew', 'loan.foreclosure', 'loan.settlement',
            'payment.view', 'payment.collect', 'payment.reverse',
            'payment.receipt.print', 'payment.receipt.reprint',
            'penalty.view', 'penalty.apply',
            'waiver.view', 'waiver.request', 'waiver.approve',
            'accounting.view',
            'document.view', 'document.upload', 'document.delete', 'document.approve',
            'report.loan', 'report.collection', 'report.overdue', 'report.portfolio', 'report.statement',
            'notification.view',
            'audit.view',
            'dashboard.branch', 'dashboard.officer',
        ]);

        $this->createRole('loan_officer', [
            'customer.view', 'customer.create', 'customer.edit', 'customer.kyc.verify',
            'loan.view', 'loan.create', 'loan.edit', 'loan.submit', 'loan.verify',
            'document.view', 'document.upload',
            'payment.view', 'payment.receipt.print',
            'report.loan', 'report.statement',
            'dashboard.officer',
        ]);

        $this->createRole('cashier', [
            'customer.view',
            'loan.view',
            'payment.view', 'payment.collect', 'payment.receipt.print', 'payment.receipt.reprint',
            'penalty.view',
            'document.view',
            'report.collection', 'report.statement',
            'dashboard.officer',
        ]);

        $this->createRole('collection_agent', [
            'customer.view',
            'loan.view',
            'payment.view', 'payment.collect', 'payment.receipt.print',
            'penalty.view',
            'dashboard.officer',
        ]);

        $this->createRole('viewer', [
            'customer.view', 'loan.view', 'payment.view',
            'penalty.view', 'document.view', 'accounting.view',
            'report.loan', 'report.collection', 'report.overdue',
            'dashboard.branch',
        ]);

        // 3. Create Super Admin user
        $superAdmin = User::firstOrCreate(
            ['email' => 'superadmin@financeerp.local'],
            [
                'name'      => 'Super Administrator',
                'password'  => Hash::make('Admin@12345'),
                'phone'     => '9999999999',
                'is_active' => true,
            ]
        );
        $superAdmin->syncRoles(['super_admin']);

        $this->command->info('✅ Roles (' . Role::count() . '), Permissions (' . Permission::count() . '), and Super Admin created.');
    }

    protected function createRole(string $name, array $permissionNames): Role
    {
        $role = Role::firstOrCreate(['name' => $name, 'guard_name' => 'web']);
        $permissions = Permission::whereIn('name', array_values($permissionNames))
            ->where('guard_name', 'web')
            ->pluck('name')
            ->toArray();
        $role->syncPermissions($permissions);
        return $role;
    }
}
