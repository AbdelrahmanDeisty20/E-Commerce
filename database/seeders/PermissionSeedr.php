<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class PermissionSeedr extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // إنشاء الأدوار
        $adminRole = Role::firstOrCreate(['name' => 'admin']);
        $userRole = Role::firstOrCreate(['name' => 'user']);

        // إنشاء الصلاحيات وربطها بالروتات
        $permissions = [
            ['name' => 'manage products', 'routes' => 'products.store,products.update,products.destroy'],
            ['name' => 'manage carts', 'routes' => 'carts.store,carts.update,carts.destroy'],
            ['name' => 'view products', 'routes' => 'products.index'],
            ['name' => 'manage orders', 'routes' => 'orders.store,orders.update,orders.destroy'],
            ['name' => 'view orders', 'routes' => 'orders.index'],
            ['name' => 'view carts', 'routes' => 'carts.index'],
        ];

        foreach ($permissions as $perm) {
            $permission = Permission::firstOrCreate(['name' => $perm['name']]);
            DB::table('permissions')
                ->where('id', $permission->id)
                ->update(['routes' => $perm['routes']]);

            // إعطاء كل صلاحية للدور المناسب
            if (in_array($perm['name'], ['manage products', 'manage orders','manage carts'])) {
                $adminRole->givePermissionTo($perm['name']);
            } else {
                $userRole->givePermissionTo($perm['name']);
            }
        }
    }
}
