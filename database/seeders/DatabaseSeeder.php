<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Illuminate\Support\Facades\Hash;
class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
    $roles = ['superadmin', 'admin', 'agent', 'user'];
    foreach ($roles as $role) {
        Role::firstOrCreate(['name' => $role]);
    }

    $permissions = [
        'c-category', 'r-category', 'u-category', 'd-category',
        'c-product', 'r-product', 'u-product', 'd-product',
        'c-banner', 'r-banner', 'u-banner', 'd-banner'
    ];

    foreach ($permissions as $permission) {
        Permission::firstOrCreate(['name' => $permission]);
    }

   
    $superAdmin = User::create([
        'name' => 'superadmin',
        'email' => 'superadmin@gmail.com',
        'password' => Hash::make('superadmin'),
    ]);
    $superAdmin->assignRole('superadmin');
    $superAdmin->syncPermissions($permissions);
    }

    
}
