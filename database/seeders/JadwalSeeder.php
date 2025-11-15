<?php

namespace Database\Seeders;

use App\Models\Jadwal;
use Illuminate\Database\Seeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class JadwalSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Jadwal::create([
            'nama_rute' => 'Jakarta - Bandung Express',
            'kota_keberangkatan' => 'Jakarta',
            'kota_tujuan' => 'Bandung',
            'waktu_keberangkatan' => now()->addDays(1)->setHour(8)->setMinute(0),
            'waktu_tiba' => now()->addDays(1)->setHour(12)->setMinute(0),
            'jumlah_kursi' => 30,
            'harga' => 150000,
            'status' => 'aktif',
        ]);

        Jadwal::create([
            'nama_rute' => 'Bandung - Yogyakarta Luxury',
            'kota_keberangkatan' => 'Bandung',
            'kota_tujuan' => 'Yogyakarta',
            'waktu_keberangkatan' => now()->addDays(2)->setHour(14)->setMinute(0),
            'waktu_tiba' => now()->addDays(2)->setHour(20)->setMinute(0),
            'jumlah_kursi' => 25,
            'jumlah_kursi_terpesan' => 0,
            'harga' => 250000,
            'status' => 'aktif',
        ]);

        Jadwal::create([
            'nama_rute' => 'Yogyakarta - Bandung Luxury',
            'kota_keberangkatan' => 'Yogyakarta',
            'kota_tujuan' => 'Bandung',
            'waktu_keberangkatan' => now()->addDays(2)->setHour(14)->setMinute(0),
            'waktu_tiba' => now()->addDays(2)->setHour(20)->setMinute(0),
            'jumlah_kursi' => 25,
            'jumlah_kursi_terpesan' => 0,
            'harga' => 250000,
            'status' => 'nonaktif',
        ]);
    }
}
