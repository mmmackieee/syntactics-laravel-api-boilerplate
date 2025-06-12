<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\Admin\UserService;

class UserController extends Controller
{
    protected UserService $users;

    public function __construct(UserService $users)
    {
        $this->users = $users;
    }

    public function index()
    {
        return response()->json([
            'data' => $this->users->all(),
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|unique:users,email',
            'password' => 'required|string|min:8|confirmed',
            'roles' => 'array',
            'roles.*' => 'string|exists:roles,name',
        ]);

        $user = $this->users->create($data);

        return response()->json([
            'message' => 'User created successfully.',
            'data' => $user,
        ], 201);
    }

    public function show($id)
    {
        return response()->json([
            'data' => $this->users->find($id),
        ]);
    }

    public function update(Request $request, $id)
    {
        $data = $request->validate([
            'name' => 'sometimes|string|max:255',
            'email' => 'sometimes|string|email|unique:users,email,' . $id,
            'password' => 'nullable|string|min:8|confirmed',
            'roles' => 'array',
            'roles.*' => 'string|exists:roles,name',
        ]);

        $user = $this->users->update($id, $data);

        return response()->json([
            'message' => 'User updated successfully.',
            'data' => $user,
        ]);
    }

    public function destroy($id)
    {
        $this->users->delete($id);

        return response()->json([
            'message' => 'User deleted successfully.',
        ]);
    }
}
