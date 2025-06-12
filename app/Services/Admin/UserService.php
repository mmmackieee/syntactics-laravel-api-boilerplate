<?php

namespace App\Services;

use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Illuminate\Support\Collection;

class RoleService
{
    /**
     * Get all roles with their permissions.
     */
    public function all(): Collection
    {
        return Role::with('permissions')->get();
    }

    /**
     * Find a role by ID.
     */
    public function find(int $id): Role
    {
        return Role::with('permissions')->findOrFail($id);
    }

    /**
     * Get all permissions.
     */
    public function getAllPermissions(): Collection
    {
        return Permission::all();
    }

    /**
     * Assign permissions to a role.
     */
    public function assignPermissions(Role $role, array $permissions): Role
    {
        $role->syncPermissions($permissions);
        return $role->load('permissions');
    }
}
