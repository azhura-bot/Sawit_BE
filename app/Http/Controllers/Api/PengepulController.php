<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\PengepulImport;
use App\Models\User;

class PengepulController extends Controller
{
    public function index()
    {
        $pengepuls = User::where('role', 'pengepul')->get();

        // Generate URL langsung dari folder public/pengepul
        $pengepuls->transform(fn($item) => tap($item, function($i) {
            $i->photo_url = $i->photo
                ? url('pengepul/' . basename($i->photo))
                : null;
        }));

        return response()->json([
            'success' => true,
            'data'    => $pengepuls,
        ], 200);
    }

    public function store(Request $request)
    {
        $v = $request->validate([
            'name'      => 'required|string|max:255',
            'email'     => 'required|email|unique:users,email',
            'no_phone'  => 'nullable|string|max:20',
            'password'  => 'required|string|min:6|confirmed',
            'photo'     => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        $photoPath = null;
        if ($file = $request->file('photo')) {
            $filename   = time() . '_' . $file->getClientOriginalName();
            $file->move(public_path('pengepul'), $filename);
            $photoPath  = 'pengepul/' . $filename;
        }

        $user = User::create([
            'name'     => $v['name'],
            'email'    => $v['email'],
            'password' => Hash::make($v['password']),
            'no_phone' => $v['no_phone'] ?? null,
            'role'     => 'pengepul',
            'photo'    => $photoPath,
        ]);

        // Set photo_url langsung
        $user->photo_url = $photoPath ? url($photoPath) : null;

        return response()->json([
            'success' => true,
            'data'    => $user,
            'message' => 'Pengepul created successfully',
        ], 201);
    }

    public function show($id)
    {
        $user = User::where('role', 'pengepul')->findOrFail($id);
        $user->photo_url = $user->photo ? url($user->photo) : null;

        return response()->json([
            'success' => true,
            'data'    => $user,
        ], 200);
    }

    public function update(Request $request, $id)
    {
        $user = User::where('role', 'pengepul')->findOrFail($id);

        $v = $request->validate([
            'name'      => 'sometimes|required|string|max:255',
            'email'     => "sometimes|required|email|unique:users,email,{$id}",
            'password'  => 'sometimes|nullable|string|min:6|confirmed',
            'no_phone'  => 'nullable|string|max:20',
            'photo'     => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        // Update fields
        if (isset($v['name']))     $user->name      = $v['name'];
        if (isset($v['email']))    $user->email     = $v['email'];
        if (!empty($v['password'])) $user->password = Hash::make($v['password']);
        if (array_key_exists('no_phone', $v)) $user->no_phone = $v['no_phone'];

        // Handle new photo
        if ($file = $request->file('photo')) {
            // hapus lama
            if ($user->photo && file_exists(public_path($user->photo))) {
                unlink(public_path($user->photo));
            }
            $filename  = time() . '_' . $file->getClientOriginalName();
            $file->move(public_path('pengepul'), $filename);
            $user->photo = 'pengepul/' . $filename;
        }

        $user->save();
        $user->photo_url = $user->photo ? url($user->photo) : null;

        return response()->json([
            'success' => true,
            'data'    => $user,
            'message' => 'Pengepul updated successfully',
        ], 200);
    }

    public function destroy($id)
    {
        $user = User::where('role', 'pengepul')->findOrFail($id);

        // hapus file foto
        if ($user->photo && file_exists(public_path($user->photo))) {
            unlink(public_path($user->photo));
        }

        $user->delete();

        return response()->json([
            'success' => true,
            'message' => 'Pengepul deleted successfully',
        ], 200);
    }

    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,xls|max:2048',
        ]);

        Excel::import(new PengepulImport, $request->file('file'));

        return response()->json([
            'success' => true,
            'message' => 'Data pengepul berhasil diunggah',
        ], 200);
    }
}
