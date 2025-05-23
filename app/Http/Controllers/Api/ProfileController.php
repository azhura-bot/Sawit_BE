<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;
use Validator;

class ProfileController extends Controller
{
    // Menampilkan data profil user
    public function showProfile()
    {
        $user = Auth::user();

        if ($user) {
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

    // Mengupdate data profil user
    public function updateProfile(Request $request)
    {
        $user = Auth::user();

        if (! $user) {
            return response()->json([
                'message' => 'User tidak ditemukan.'
            ], 404);
        }

        // Validasi data input
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

        // Simpan perubahan data
        $user->name     = $request->name;
        $user->email    = $request->email;
        $user->no_phone = $request->no_phone;

        // Proses upload foto baru jika ada
        if ($file = $request->file('photo')) {
            // Buat folder public/photos jika belum ada
            $photoPath = public_path('photos');
            if (!File::exists($photoPath)) {
                File::makeDirectory($photoPath, 0755, true);
            }

            // Hapus foto lama jika ada
            if ($user->photo && file_exists(public_path($user->photo))) {
                @unlink(public_path($user->photo));
            }

            // Simpan file baru
            $filename = time() . '_' . $file->getClientOriginalName();
            $file->move($photoPath, $filename);
            $user->photo = 'photos/' . $filename;
        }

        $user->save();

        // Tambahkan URL untuk foto
        $user->photo_url = $user->photo
            ? url($user->photo)
            : null;

        return response()->json([
            'message' => 'Profil berhasil diperbarui.',
            'data'    => $user
        ]);
    }
}
