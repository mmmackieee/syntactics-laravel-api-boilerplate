<?php

namespace App\Services\Admin;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Collection;
use Spatie\Permission\Models\Role;

class UserService
{
    /**
     * Get all users with their roles.
     */
    public function all(): Collection
    {
        return User::with('roles')->get();
    }

    /**
     * Find a user by ID with roles.
     */
    public function find(int $id): User
    {
        return User::with('roles')->findOrFail($id);
    }

    /**
     * Create a new user and optionally assign roles.
     */
    public function create(array $data): User
    {
        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
        ]);

        if (!empty($data['roles'])) {
            $user->syncRoles($data['roles']);
        }

        return $user->load('roles');
    }

    /**
     * Update user info and roles.
     */
    public function update(User $user, array $data): User
    {
        $user->update([
            'name' => $data['name'],
            'email' => $data['email'],
        ]);

        if (!empty($data['password'])) {
            $user->update(['password' => Hash::make($data['password'])]);
        }

        if (isset($data['roles'])) {
            $user->syncRoles($data['roles']);
        }

        return $user->load('roles');
    }

    /**
     * Delete or deactivate the user.
     */
    public function delete(User $user): void
    {
        $user->delete(); // or soft delete if applicable
    }

    /**
     * Return all roles for admin UI.
     */
    public function getAllRoles(): Collection
    {
        return Role::all();
    }
}
