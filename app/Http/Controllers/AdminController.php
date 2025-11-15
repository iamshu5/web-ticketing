<?php

namespace App\Http\Controllers;

use Inertia\Inertia;
use App\Models\Jadwal;
use App\Models\Booking;
use App\Models\SuratJalan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AdminController extends Controller
{

    public function dashboard()
{
    if (!Auth::check()) {
        return redirect('/login');
    }
    
    if (Auth::user()->role !== 'admin') {
        abort(403, 'Hanya admin yang bisa akses.');
    }

    $pendingBookings = Booking::with(['jadwal', 'user'])
        ->where('status', 'dibayar')
        ->latest()
        ->get();

    $jadwal = Jadwal::with(['booking' => function($query) {
        $query->whereIn('status', ['pending', 'dibayar', 'dikonfirmasi'])
            ->with('details');
    }])->latest()->get()->map(function ($jadwal) {
        $jumlahKursiTerpesan = 0;
        foreach ($jadwal->booking as $booking) {
            $jumlahKursiTerpesan += $booking->details->count();
        }
        $jadwal->jumlah_kursi_terpesan = $jumlahKursiTerpesan;
        return $jadwal;
    });

    return Inertia::render('Admin/Dashboard', [
        'pendingBookings' => $pendingBookings,
        'jadwal' => $jadwal,
    ]);
}

    public function konfirmasiPemesanan($id)
    {
        if (!Auth::check() || Auth::user()->role !== 'admin') {
            abort(403, 'Hanya admin yang bisa akses.');
        }

        $booking = Booking::findOrFail($id);
        $booking->update(['status' => 'dikonfirmasi']);

        return back()->with('sukses', 'Booking berhasil dikonfirmasi.');
    }

    public function buatSuratJalan(Request $request)
    {
        $request->validate([
            'pemesanan_id' => 'required|exists:pemesanan,id',
            'nama_supir' => 'required|string|max:255',
            'nomor_kendaraan' => 'required|string|max:20',
            'jenis_kendaraan' => 'required|string|max:50',
        ]);

        SuratJalan::create($request->all());

        return back()->with('sukses', 'Surat jalan berhasil dibuat.');
    }

    public function simpanJadwal(Request $request)
    {
        $request->validate([
            'nama_rute' => 'required|string|max:255',
            'kota_keberangkatan' => 'required|string|max:255',
            'kota_tujuan' => 'required|string|max:255',
            'waktu_keberangkatan' => 'required|date',
            'waktu_tiba' => 'required|date',
            'jumlah_kursi' => 'required|integer|min:1',
            'harga' => 'required|numeric|min:0',
        ]);

        Jadwal::create($request->all());

        return back()->with('sukses', 'Jadwal berhasil disimpan.');
    }
}