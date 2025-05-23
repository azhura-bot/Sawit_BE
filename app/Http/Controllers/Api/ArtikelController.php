<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Artikel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ArtikelController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $artikels = Artikel::all();
        return response()->json([
            'success' => true,
            'data'    => $artikels,
        ], 200);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $messages = [
            'title.required'   => 'Judul artikel wajib diisi.',
            'content.required' => 'Konten artikel wajib diisi.',
            'image.image'      => 'File gambar harus berupa gambar.',
            'image.max'        => 'Ukuran gambar maksimal 2MB.',
        ];
    
        $data = $request->validate([
            'title'   => 'required|string|max:255',
            'content' => 'required|string',
            'image'   => 'nullable|image|max:2048',
        ], $messages);
    
        if ($request->hasFile('image')) {
            $file = $request->file('image');
            $filename = time() . '_' . $file->getClientOriginalName();
            $file->move(public_path('images/artikels'), $filename);
            $data['image'] = 'images/artikels/' . $filename;
        }

    
        $artikel = Artikel::create($data);
    
        return response()->json([
            'success' => true,
            'data'    => $artikel,
            'message' => 'Artikel berhasil dibuat',
        ], 201);
    }
    

    /**
     * Display the specified resource.
     */
    public function show(Artikel $artikel)
    {
        return response()->json([
            'success' => true,
            'data'    => $artikel,
        ], 200);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Artikel $artikel)
    {
        $messages = [
            'title.required'   => 'Judul artikel wajib diisi.',
            'content.required' => 'Konten artikel wajib diisi.',
            'image.image'      => 'File gambar harus berupa gambar.',
            'image.max'        => 'Ukuran gambar maksimal 2MB.',
        ];
    
        $data = $request->validate([
            'title'   => 'required|string|max:255',
            'content' => 'required|string',
            'image'   => 'nullable|image|max:2048',
        ], $messages);
    
        if ($request->hasFile('image')) {
            // hapus file lama jika ada
            if ($artikel->image && file_exists(public_path($artikel->image))) {
                unlink(public_path($artikel->image));
            }

            $file = $request->file('image');
            $filename = time() . '_' . $file->getClientOriginalName();
            $file->move(public_path('images/artikels'), $filename);
            $data['image'] = 'images/artikels/' . $filename;
        }

    
        $artikel->update($data);
    
        return response()->json([
            'success' => true,
            'data'    => $artikel,
            'message' => 'Artikel berhasil diperbarui',
        ], 200);
    }    

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Artikel $artikel)
    {
        // hapus file image bila ada
        if ($artikel->image && file_exists(public_path($artikel->image))) {
            unlink(public_path($artikel->image));
        }
        $artikel->delete();

        return response()->json([
            'success' => true,
            'message' => 'Artikel berhasil dihapus',
        ], 200);
    }
}
