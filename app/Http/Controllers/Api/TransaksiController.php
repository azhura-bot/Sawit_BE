<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Transaksi;
use App\Models\DaftarHarga;
use App\Models\Task;

class TransaksiController extends Controller
{
    public function index()
    {
        $transaksi = Transaksi::with([
            'task.janjiTemu',
            'task.pengepul' // relasi pengepul ke user
        ])->get();

        return response()->json([
            'success' => true,
            'data' => $transaksi
        ]);
    }

    // Code ketika pengepul menemui petani dan mencatat transaksi
    public function store(Request $request)
    {
        // Validasi input yang benar (sesuaikan nama field)
        $validated = $request->validate([
            'task_id' => 'required|exists:tasks,id',
            'jumlah' => 'required|numeric|min:0',
        ], [
            'task_id.required' => 'Kolom Tugas harus diisi.',
            'task_id.exists' => 'Tugas yang dipilih tidak valid.',
            'jumlah.required' => 'Kolom Jumlah harus diisi.',
            'jumlah.numeric' => 'Kolom Jumlah harus berupa angka.',
            'jumlah.min' => 'Kolom Jumlah tidak boleh kurang dari 0.',
        ]);

        // Ambil harga terbaru
        $hargaTerbaru = DaftarHarga::latest()->first();
        if (!$hargaTerbaru) {
            return response()->json([
                'success' => false,
                'message' => 'Harga belum tersedia',
            ], 400);
        }

        $totalHarga = $validated['jumlah'] * $hargaTerbaru->harga;

        // Simpan transaksi
        $transaksi = Transaksi::create([
            'task_id' => $validated['task_id'],
            'jumlah' => $validated['jumlah'],
            'total_harga' => $totalHarga,
        ]);

        // Ubah status task menjadi completed
        $task = Task::find($validated['task_id']);
        if ($task) {
            $task->status = 'completed'; // sesuaikan dengan nilai status yang kamu pakai
            $task->save();
        }

        return response()->json([
            'success' => true,
            'data' => $transaksi,
            'message' => 'Transaksi berhasil ditambahkan dan status task diubah menjadi completed',
        ], 201);
    }

    public function update(Request $request, $id)
    {
        // Validasi hanya field total_harga
        $validated = $request->validate([
            'total_harga' => 'required|numeric|min:0',
        ], [
            'total_harga.required' => 'Kolom Total Harga harus diisi.',
            'total_harga.numeric'  => 'Kolom Total Harga harus berupa angka.',
            'total_harga.min'      => 'Kolom Total Harga tidak boleh kurang dari 0.',
        ]);

        // Cari transaksi atau 404 jika tidak ada
        $transaksi = Transaksi::findOrFail($id);

        // Update hanya total_harga
        $transaksi->update([
            'total_harga' => $validated['total_harga'],
        ]);

        // Kembalikan response success
        return response()->json([
            'success' => true,
            'data'    => $transaksi,
            'message' => 'Transaksi berhasil diperbarui.',
        ]);
    }


    public function destroy($id)
    {
        $transaksi = Transaksi::findOrFail($id);
        $transaksi->delete();

        return response()->json([
            'success' => true,
            'message' => 'Transaksi berhasil dihapus',
        ]);
    }
}
