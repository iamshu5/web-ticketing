<?php

namespace App\Http\Controllers;

use Inertia\Inertia;
use App\Models\Jadwal;
use App\Models\Booking;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class CheckerController extends Controller
{
    public function dashboard()
    {
        if (!Auth::check() || Auth::user()->role !== 'checker') {
            abort(403, 'Hanya checker yang bisa akses.');
        }
        
        $bookings = Booking::with(['jadwal', 'details'])
            ->whereHas('jadwal', function($query) {
                $query->whereDate('waktu_keberangkatan', '=', now()->toDateString());
            })
            ->whereIn('status', ['dikonfirmasi', 'selesai'])
            ->latest()
            ->get();

        $jadwalHariIni = Jadwal::whereDate('waktu_keberangkatan', now()->toDateString())
            ->get(['nama_rute', 'kota_keberangkatan', 'kota_tujuan', 'waktu_keberangkatan']);

        return Inertia::render('Checker/Dashboard', [
            'bookings' => $bookings,
            'jadwalHariIni' => $jadwalHariIni,
        ]);
    }

    public function periksaPenumpang($id)
    {
        if (!Auth::check() || Auth::user()->role !== 'checker') {
            abort(403, 'Hanya checker yang bisa akses.');
        }
        
        $booking = Booking::findOrFail($id);
        
        if ($booking->status !== 'dikonfirmasi') {
            return back()->with('error', 'Hanya bisa memeriksa penumpang dengan status dikonfirmasi');
        }
        
        $booking->update(['status' => 'selesai']);

        return back()->with('success', 'Penumpang berhasil diperiksa');
    }
}