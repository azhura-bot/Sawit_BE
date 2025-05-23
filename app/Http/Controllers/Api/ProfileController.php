<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Validator;

class ProfileController extends Controller
{
    // Menampilkan data profil
    public function showProfile()
    {
        $user = Auth::user();

        if ($user) {
            // Bangun URL foto langsung dari folder public
            $user->photo_url = $user->photo
                ? url($user->photo)
                : null;

            return response()->json([
                'data' => $user
            ]);
        }

        return response()->json([
            'message' => 'User tidak ditemukan.'
        ], 404);
    }

    // Update data profil
    public function updateProfile(Request $request)
    {
        $user = Auth::user();

        if (! $user) {
            return response()->json([
                'message' => 'User tidak ditemukan.'
            ], 404);
        }

        // Validasi input
        $validator = Validator::make($request->all(), [
            'name'     => 'required|string|max:255',
            'email'    => 'required|email|max:255|unique:users,email,' . $user->id,
            'no_phone' => 'nullable|string|max:20',
            'photo'    => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'errors' => $validator->errors()
            ], 400);
        }

        // Assign fields
        $user->name     = $request->name;
        $user->email    = $request->email;
        $user->no_phone = $request->no_phone;

        // Jika ada foto baru
        if ($file = $request->file('photo')) {
            // Hapus lama
            if ($user->photo && file_exists(public_path($user->photo))) {
                unlink(public_path($user->photo));
            }
            // Simpan langsung ke public/photos
            $filename     = time() . '_' . $file->getClientOriginalName();
            $file->move(public_path('photos'), $filename);
            $user->photo = 'photos/' . $filename;
        }

        $user->save();

        // Tambah URL foto
        $user->photo_url = $user->photo
            ? url($user->photo)
            : null;

        return response()->json([
            'message' => 'Profil berhasil diperbarui.',
            'data'    => $user
        ]);
    }
}
