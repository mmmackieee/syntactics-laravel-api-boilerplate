<?php

namespace App\Services;

use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Illuminate\Support\Collection;
use Illuminate\Database\Eloquent\ModelNotFoundException;

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
     * Get a specific role by ID with permissions.
     */
    public function find(int $id): Role
    {
        return Role::with('permissions')->findOrFail($id);
    }

    /**
     * Create a new role and optionally assign permissions.
     */
    public function create(array $data): Role
    {
        $role = Role::create(['name' => $data['name']]);

        if (!empty($data['permissions'])) {
            $role->syncPermissions($data['permissions']);
        }

        return $role->load('permissions');
    }

    /**
     * Update an existing role's name and permissions.
     */
    public function update(Role $role, array $data): Role
    {
        $role->update(['name' => $data['name']]);

        if (isset($data['permissions'])) {
            $role->syncPermissions($data['permissions']);
        }

        return $role->load('permissions');
    }

    /**
     * Delete a role.
     */
    public function delete(Role $role): void
    {
        $role->delete();
    }

    /**
     * Assign permissions to a role.
     */
    public function assignPermissions(Role $role, array $permissions): Role
    {
        $role->syncPermissions($permissions);
        return $role->load('permissions');
    }

    /**
     * Get all available permissions.
     */
    public function getAllPermissions(): Collection
    {
        return Permission::all();
    }
}
