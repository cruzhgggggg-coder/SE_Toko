<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class UserController extends Controller
{
    public function index()
    {
        // Only return safe fields — never expose password hashes
        $users = User::select('id', 'name', 'username', 'role', 'created_at', 'last_login')
            ->get();

        return response()->json($users);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'username' => 'required|string|min:4|max:50|unique:users,username',
            'password' => ['required', 'string', 'min:8', Password::min(8)
                ->mixedCase()
                ->numbers()],
            'role' => 'required|string|in:admin,kasir', // Cannot create owner via API
        ]);

        $validated['password'] = Hash::make($validated['password']);

        $user = User::create($validated);

        return response()->json([
            'id' => $user->id,
            'name' => $user->name,
            'username' => $user->username,
            'role' => $user->role,
        ], 201);
    }

    public function show(string $id)
    {
        if (!is_numeric($id)) {
            return response()->json(['message' => 'ID pengguna tidak valid.'], 422);
        }

        $user = User::select('id', 'name', 'username', 'role', 'created_at', 'last_login')
            ->findOrFail($id);

        return response()->json($user);
    }

    public function update(Request $request, string $id)
    {
        $user = User::findOrFail($id);

        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'username' => 'sometimes|string|min:4|max:50|unique:users,username,' . $id,
            'password' => 'nullable|string|min:8',
            'role' => 'sometimes|string|in:admin,kasir', // Cannot promote to owner
        ]);

        if (isset($validated['password'])) {
            $validated['password'] = Hash::make($validated['password']);
        } else {
            unset($validated['password']);
        }

        $user->update($validated);

        return response()->json([
            'id' => $user->id,
            'name' => $user->name,
            'username' => $user->username,
            'role' => $user->role,
        ]);
    }

    public function destroy(string $id)
    {
        $user = User::findOrFail($id);

        // Prevent deleting any owner account via API
        if ($user->role === 'owner') {
            return response()->json(['message' => 'Tidak dapat menghapus akun owner.'], 403);
        }

        // Prevent self-deletion
        if ($user->id === auth()->id()) {
            return response()->json(['message' => 'Tidak dapat menghapus akun sendiri.'], 403);
        }

        $user->delete();
        return response()->json(null, 204);
    }
}