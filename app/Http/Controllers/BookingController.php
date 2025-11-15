<?php

namespace App\Http\Controllers;

use Inertia\Inertia;
use App\Models\Jadwal;
use App\Models\Booking;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Models\BookingDetail;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class BookingController extends Controller
{
    public function index()
    {
        if (!Auth::check() || Auth::user()->role !== 'customer') {
            abort(403, 'Hanya customer yang bisa akses.');
        }
        
        $bookings = Booking::with(['jadwal', 'details'])
            ->where('user_id', Auth::id())
            ->latest()
            ->get();

        return Inertia::render('Booking/Index', ['bookings' => $bookings]);
    }

    public function buat()
    {
        if (!Auth::check() || Auth::user()->role !== 'customer') {
            abort(403, 'Hanya customer yang bisa akses.');
        }
        
        $jadwal = Jadwal::where('status', 'aktif')
            ->where('waktu_keberangkatan', '>', now())
            ->get()
            ->map(function ($jadwal) {
                $jumlahKursiTerpesan = BookingDetail::whereHas('booking', function($query) use ($jadwal) {
                    $query->where('jadwal_id', $jadwal->id)->whereIn('status', ['pending', 'dibayar', 'dikonfirmasi']);
                })->count();
                
                $kursiTerpesan = BookingDetail::whereHas('booking', function($query) use ($jadwal) {
                    $query->where('jadwal_id', $jadwal->id)->whereIn('status', ['pending', 'dibayar', 'dikonfirmasi']);
                })->pluck('nomor_kursi')->toArray();
                
                $jadwal->jumlah_kursi_terpesan = $jumlahKursiTerpesan;
                $jadwal->jumlah_kursi_tersedia = $jadwal->jumlah_kursi - $jumlahKursiTerpesan;
                $jadwal->kursi_terpesan = $kursiTerpesan;
                
                return $jadwal;
            });

        return Inertia::render('Booking/Buat', ['jadwal' => $jadwal]);
    }

    public function simpan(Request $request)
    {
        if (!Auth::check() || Auth::user()->role !== 'customer') {
            abort(403, 'Hanya customer yang bisa akses.');
        }
        
        $request->validate([
            'jadwal_id' => 'required|exists:jadwal,id',
            'penumpang' => 'required|array|min:1',
            'penumpang.*.nomor_kursi' => 'required|integer|min:1',
            'penumpang.*.nama_penumpang' => 'required|string|max:255',
            'penumpang.*.telepon_penumpang' => 'required|string|max:15',
        ]);

        $jadwal = Jadwal::findOrFail($request->jadwal_id);
        
        $kursiTerpesanDiDatabase = BookingDetail::whereHas('booking', function($query) use ($request) {
            $query->where('jadwal_id', $request->jadwal_id)
                ->whereIn('status', ['pending', 'dibayar', 'dikonfirmasi']);
        })->pluck('nomor_kursi')->unique()->count();

        $jumlahKursiTersedia = $jadwal->jumlah_kursi - $kursiTerpesanDiDatabase;
        
        if (count($request->penumpang) > $jumlahKursiTersedia) {
            return back()->withErrors([
                'penumpang' => 'Jumlah penumpang (' . count($request->penumpang) . ') melebihi kursi tersedia (' . $jumlahKursiTersedia . ').'
            ]);
        }

        $kursiInput = collect($request->penumpang)->pluck('nomor_kursi');
        $duplicateKursi = $kursiInput->duplicates();
        if ($duplicateKursi->isNotEmpty()) {
            return back()->withErrors([
                'nomor_kursi' => 'Kursi ' . $duplicateKursi->implode(', ') . ' dipilih lebih dari satu kali.'
            ]);
        }

        $kursiTerpesan = [];
        $kursiSudahDipesanDiDB = BookingDetail::whereHas('booking', function($query) use ($request) {
            $query->where('jadwal_id', $request->jadwal_id)
                ->whereIn('status', ['pending', 'dibayar', 'dikonfirmasi']);
        })->pluck('nomor_kursi')->toArray();

        foreach ($request->penumpang as $penumpang) {
            $kursi = $penumpang['nomor_kursi'];
            
            if ($kursi > $jadwal->jumlah_kursi) {
                return back()->withErrors([
                    'nomor_kursi' => 'Kursi ' . $kursi . ' tidak tersedia. Bus hanya memiliki ' .  $jadwal->jumlah_kursi . ' kursi.'
                ]);
            }

            if (in_array($kursi, $kursiSudahDipesanDiDB)) {
                $kursiTerpesan[] = $kursi;
            }
        }

        if (!empty($kursiTerpesan)) {
            return back()->withErrors(['nomor_kursi' => 'Kursi ' . implode(', ', $kursiTerpesan) . ' sudah dipesan.']);
        }

        DB::beginTransaction();
        try {
            $booking = Booking::create([
                'user_id' => Auth::id(),
                'jadwal_id' => $request->jadwal_id,
                'kode_pemesanan' => 'PSN-' . Str::random(8),
                'status' => 'pending',
                'nomor_kursi' => $request->penumpang[0]['nomor_kursi'],
                'nama_penumpang' => $request->penumpang[0]['nama_penumpang'],
                'telepon_penumpang' => $request->penumpang[0]['telepon_penumpang'],
                'catatan' => count($request->penumpang) > 1 ? 'Pemesanan untuk ' . count($request->penumpang) . ' penumpang' : null
            ]);

            foreach ($request->penumpang as $penumpang) {
                $booking->details()->create([
                    'nomor_kursi' => $penumpang['nomor_kursi'],
                    'nama_penumpang' => $penumpang['nama_penumpang'],
                    'telepon_penumpang' => $penumpang['telepon_penumpang'],
                ]);
            }

            $totalKursiTerpesanBaru = $kursiTerpesanDiDatabase + count($request->penumpang);
            $jadwal->update([
                'jumlah_kursi_terpesan' => $totalKursiTerpesanBaru
            ]);

            DB::commit();

            return redirect()->route('booking.konfirmasi', $booking->id);

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Terjadi kesalahan sistem: ' . $e->getMessage()]);
        }
    }

    public function konfirmasi($id)
    {
        if (!Auth::check() || Auth::user()->role !== 'customer') {
            abort(403, 'Hanya customer yang bisa akses.');
        }
        
        $booking = Booking::with(['jadwal', 'details'])
            ->where('user_id', Auth::id())
            ->findOrFail($id);

        return inertia('Booking/Konfirmasi', ['booking' => $booking]);
    }

    public function batal($id)
    {
        if (!Auth::check() || Auth::user()->role !== 'customer') {
            abort(403, 'Hanya customer yang bisa akses.');
        }
        
        $booking = Booking::with('jadwal', 'details')->where('user_id', Auth::id())->findOrFail($id);
        
        if (in_array($booking->status, ['pending', 'dibayar'])) {
            DB::transaction(function() use ($booking) {
                $jumlahPenumpang = $booking->details->count();
                $booking->jadwal->decrement('jumlah_kursi_terpesan', $jumlahPenumpang);
                
                $booking->update(['status' => 'dibatalkan']);
            });
        }

        return redirect()->route('booking.index')->with('success', 'Pemesanan berhasil dibatalkan.');
    }

    public function unggahBuktiPembayaran(Request $request, $id)
    {
        if (!Auth::check() || Auth::user()->role !== 'customer') {
            abort(403, 'Hanya customer yang bisa akses.');
        }
        
        $request->validate([
            'bukti_pembayaran' => 'required|image|max:2048',
        ]);

        $booking = Booking::where('user_id', Auth::id())->findOrFail($id);

        if ($request->hasFile('bukti_pembayaran')) {
            $path = $request->file('bukti_pembayaran')->store('bukti-pembayaran', 'public');
            $booking->update([
                'bukti_pembayaran' => $path,
                'tanggal_pembayaran' => now(),
                'status' => 'dibayar',
            ]);
        }

        return redirect()->route('booking.index');
    }
}