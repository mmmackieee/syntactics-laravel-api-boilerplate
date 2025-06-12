<?php

namespace App\Services;

use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RoleService
{
    public function all()
    {
        return Role::with('permissions')->get();
    }

    public function find($id)
    {
        return Role::with('permissions')->findOrFail($id);
    }

    public function create(array $data)
    {
        $role = Role::create(['name' => $data['name']]);

        if (!empty($data['permissions'])) {
            $role->syncPermissions($data['permissions']);
        }

        return $role->load('permissions');
    }

    public function update(Role $role, array $data)
    {
        $role->update(['name' => $data['name']]);

        if (isset($data['permissions'])) {
            $role->syncPermissions($data['permissions']);
        }

        return $role->load('permissions');
    }

    public function delete(Role $role)
    {
        $role->delete();
    }

    public function getAllPermissions()
    {
        return Permission::all();
    }
}
