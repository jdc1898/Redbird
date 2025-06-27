<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RolesAndPermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        /**
         * These are the default application roles and permissions. They should
         * only need to be loaded once when the application is first installed.
         * If nothing is defined in the config file, the seeder will not run.
         */
        $rolesAndPermissions = config('redbird.seed', []);

        if (empty($rolesAndPermissions)) {
            return;
        }

        /**
         * Loop through the roles and permissions and create them in the database.
         * This will create the roles and permissions in the database if they do not
         * already exist. If they do exist, they will be updated with the new values.
         * Be cautious when running this seeder as it will overwrite existing roles
         * and permissions with the values defined above.
         */
        foreach ($rolesAndPermissions as $roleName => $roleData) {

            $role = Role::updateOrCreate([
                'name' => $roleData['name'] ?? $roleName,
            ],
                [
                    'guard_name' => $roleData['role_guard'] ?? 'web',
                    'display_name' => $roleData['name'],
                    'description' => $roleData['description'] ?? null,
                ]
            );

            foreach ($roleData['guards'] as $guard) {

                foreach ($guard['permissions'] as $permissionData) {

                    $permission = Permission::updateOrCreate([
                        'name' => $permissionData['name'],
                    ],
                        [
                            'guard_name' => $guard['name'],
                            'description' => $permissionData['description'],
                            'display_name' => $permissionData['display_name'] ?? null,
                        ]
                    );
                }

                $role->permissions()->syncWithoutDetaching($permission->id);
            }
        }
    }
}
