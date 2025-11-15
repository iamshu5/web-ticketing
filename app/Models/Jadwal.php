<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Jadwal extends Model
{
    protected $table = 'jadwal';
    protected $guarded = ['id'];

    protected $casts = [
        'waktu_keberangkatan' => 'datetime',
        'waktu_tiba' => 'datetime',
    ];

    public function booking()
    {
        return $this->hasMany(Booking::class, 'jadwal_id');
    }

    public function getKursiTersedia()
    {
        $kursiTerpesan = $this->booking()->whereIn('status', ['pending', 'dibayar', 'dikonfirmasi'])
            ->pluck('nomor_kursi')
            ->toArray();

        $semuaKursi = range(1, $this->jumlah_kursi);
        
        return array_diff($semuaKursi, $kursiTerpesan);
    }

    public function getJumlahKursiTerpesan()
    {
        return $this->booking()->whereIn('status', ['pending', 'dibayar', 'dikonfirmasi'])->count();
    }
}
