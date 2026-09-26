<?php

namespace Database\Seeders;

use App\Models\Company;
use App\Models\Branch;
use App\Models\User;
use App\Models\ChartOfAccount;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        $this->call([
            RolesPermissionsSeeder::class,
            CompanySeeder::class,
            ChartOfAccountsSeeder::class,
        ]);
    }
}
