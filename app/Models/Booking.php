<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Booking extends Model
{
    protected $table = 'booking';

    protected $guarded = ['id'];

    protected $casts = [
        'tanggal_pembayaran' => 'datetime',
    ];

    public function details()
    {
        return $this->hasMany(BookingDetail::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function jadwal()
    {
        return $this->belongsTo(Jadwal::class, 'jadwal_id');
    }

    public function suratJalan()
    {
        return $this->hasOne(SuratJalan::class, 'booking_id');
    }

    public function isMultiple()
    {
        return $this->details()->count() > 0;
    }

    public function totalPenumpang()
    {
        return $this->isMultiple() ? $this->details()->count() : 1;
    }
}
