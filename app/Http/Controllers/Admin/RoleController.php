<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\RoleService;
use Spatie\Permission\Models\Role;
class RoleController extends Controller
{
    protected RoleService $roles;

    public function __construct(RoleService $roles)
    {
        $this->roles = $roles;
    }

    public function index()
    {
        return response()->json([
            'data' => $this->roles->all(),
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|unique:roles,name',
            'permissions' => 'array',
            'permissions.*' => 'string|exists:permissions,name',
        ]);

        $role = $this->roles->create($data);

        return response()->json([
            'message' => 'Role created successfully.',
            'data' => $role,
        ], 201);
    }

    public function show($id)
    {
        $role = $this->roles->find($id);

        return response()->json([
            'data' => $role,
        ]);
    }

    public function update(Request $request, $id)
    {
        $role = $this->roles->find($id);

        $data = $request->validate([
            'name' => 'required|string|unique:roles,name,' . $role->id,
            'permissions' => 'array',
            'permissions.*' => 'string|exists:permissions,name',
        ]);

        $updatedRole = $this->roles->update($role, $data);

        return response()->json([
            'message' => 'Role updated successfully.',
            'data' => $updatedRole,
        ]);
    }

    public function destroy($id)
    {
        $role = $this->roles->find($id);

        $this->roles->delete($role);

        return response()->json([
            'message' => 'Role deleted successfully.',
        ]);
    }

    public function permissions()
    {
        return response()->json([
            'data' => $this->roles->getAllPermissions(),
        ]);
    }
}
