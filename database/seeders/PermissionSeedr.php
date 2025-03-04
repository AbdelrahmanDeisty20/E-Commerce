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
    {
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();
        $adminRole = Role::firstOrCreate(['name' => 'admin']);
        $userRole = Role::firstOrCreate(['name' => 'customer']);
        $permissions = [
            'manage products' => 'products.store,products.update,products.destroy',
            'manage carts' => 'carts.store,carts.update,carts.destroy',
            'view products' => 'products.index',
            'manage orders' => 'orders.store,orders.update,orders.destroy',
            'view orders' => 'orders.index',
            'view carts' => 'carts.index',
        ];
        foreach ($permissions as $permissionName => $routes) {
            $permission = Permission::firstOrCreate(['name' => $permissionName]);
            DB::table('permissions')->where('id', $permission->id)->update(['routes' => $routes]);
            if (in_array($permissionName, ['manage products', 'manage orders', 'manage carts'])) {
                $adminRole->givePermissionTo($permission);
            } else {
                $userRole->givePermissionTo($permission);
            }
        }
    }
}
