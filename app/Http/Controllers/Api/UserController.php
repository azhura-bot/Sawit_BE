<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    /**
     * Tampilkan semua user.
     */
    public function index()
    {
        $users = User::all();
        return response()->json([
            'data' => $users,
        ], 200);
    }

    /**
     * Buat user baru.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'     => 'required|string|max:255',
            'email'    => 'required|email|unique:users,email',
            'password' => 'required|string|min:6|confirmed',
            'phone'    => 'nullable|string|max:20',
        ]);

        $user = User::create([
            'name'     => $validated['name'],
            'email'    => $validated['email'],
            'password' => Hash::make($validated['password']),
            'phone'    => $validated['phone'] ?? null,
        ]);

        return response()->json([
            'data'    => $user,
            'message' => 'User created successfully',
        ], 201);
    }

    /**
     * Tampilkan user tertentu.
     */
    public function show(string $id)
    {
        $user = User::findOrFail($id);
        return response()->json([
            'data' => $user,
        ], 200);
    }

    /**
     * Update user.
     */
    public function update(Request $request, string $id)
    {
        $user = User::findOrFail($id);

        $validated = $request->validate([
            'name'     => 'sometimes|required|string|max:255',
            'email'    => "sometimes|required|email|unique:users,email,{$id}",
            'password' => 'sometimes|nullable|string|min:6|confirmed',
            'phone'    => 'nullable|string|max:20',
        ]);

        if (isset($validated['name'])) {
            $user->name = $validated['name'];
        }
        if (isset($validated['email'])) {
            $user->email = $validated['email'];
        }
        if (! empty($validated['password'])) {
            $user->password = Hash::make($validated['password']);
        }
        if (array_key_exists('phone', $validated)) {
            $user->phone = $validated['phone'];
        }

        $user->save();

        return response()->json([
            'data'    => $user,
            'message' => 'User updated successfully',
        ], 200);
    }

    /**
     * Hapus user.
     */
    public function destroy(string $id)
    {
        $user = User::findOrFail($id);
        $user->delete();

        return response()->json([
            'message' => 'User deleted successfully',
        ], 200);
    }
}
