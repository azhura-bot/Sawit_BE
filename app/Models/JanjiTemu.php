<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class JanjiTemu extends Model
{
    use HasFactory;

    // Tambahkan kolom alasan_reject jika belum ada di migrasi
    protected $table = 'janji_temu';
    protected $fillable = [
        'nama_petani',
        'email',
        'alamat',
        'no_hp',
        'tanggal',
        'latitude',
        'longitude',
        'status',
        'alasan_reject',
    ];

        protected $casts = [
        'tanggal' => 'datetime',
    ];

    public function tasks() {
    return $this->hasMany(Task::class, 'janji_temu_id');
    }
}