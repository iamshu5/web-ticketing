<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('jadwal', function (Blueprint $table) {
            $table->id();
            $table->string('nama_rute');
            $table->string('kota_keberangkatan');
            $table->string('kota_tujuan');
            $table->dateTime('waktu_keberangkatan');
            $table->dateTime('waktu_tiba');
            $table->integer('jumlah_kursi');
            $table->integer('jumlah_kursi_terpesan')->default(0);
            $table->decimal('harga', 10, 2);
            $table->enum('status', ['aktif', 'nonaktif'])->default('aktif');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('jadwal');
    }
};
