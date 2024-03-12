<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('staffs', function (Blueprint $table) {
            //main
            $table->bigIncrements("KODE");
            $table->string('EMAIL');
            $table->string('NAMA');
            $table->string('NICKNAME')->nullable();
            $table->string('KODE_JABATAN');
            $table->string('KODE_LOKASI');
            $table->string('NIK');
            $table->string('NO_SIM');
            $table->string('ALAMAT_KTP');
            $table->string('ALAMAT_DOMISILI');
            $table->date('TTL');
            $table->string('JENIS_KELAMIN');
            $table->string('AGAMA');
            $table->string('STATUS_PERNIKAHAN');
            $table->integer('JUMLAH_ANAK');
            $table->string('NO_HP');
            $table->string('NO_HP_KANTOR');
            $table->string('NO_HP_KELUARGA');
            $table->string('KETERANGAN_KELUARGA');
            $table->date('TGL_MULAI_KERJA');
            $table->date('TGL_SELESAI_KONTRAK');
            $table->string('STATUS_KARYAWAN');
            $table->time('JAM_MASUK');
            $table->time('JAM_KELUAR');
            $table->string('ACCOUNT_NUMBER');
            $table->string('BANK');
            $table->string('ATAS_NAMA');
            //fasilitas karyawan
            $table->string('GAJI_POKOK');
            $table->string('BPJS_KESEHATAN');
            $table->string('BPJS_KETENAGAKERJAAN');
            $table->string('UANG_MAKAN');
            $table->string('UANG_TRANSPORT');
            $table->string('UANG_LEMBUR');
            $table->string('PULSA');
            $table->string('INSENTIF');
            $table->string('THR');
            $table->string('TUNJANGAN_KENDARAAN');
            $table->string('TUNJANGAN_LAIN');
            //detail gaji dan tunjangan karyawan
            $table->unsignedBigInteger('DET_GAJI_POKOK')->nullable();
            $table->unsignedBigInteger('DET_BPJS_KESEHATAN')->nullable();
            $table->unsignedBigInteger('DET_BPJS_KETENAGAKERJAAN')->nullable();
            $table->unsignedBigInteger('DET_UANG_MAKAN')->nullable();
            $table->unsignedBigInteger('DET_UANG_TRANSPORT')->nullable();
            $table->unsignedBigInteger('DET_UANG_LEMBUR')->nullable();
            $table->unsignedBigInteger('DET_PULSA')->nullable();
            $table->unsignedBigInteger('DET_TUNJANGAN_KENDARAAN')->nullable();
            $table->unsignedBigInteger('DET_TUNJANGAN_LAIN')->nullable();
            $table->string('KETERANGAN_TUNJANGAN_LAIN')->nullable();
            //upload dokumen
            $table->string('FOTO_KTP')->nullable();
            $table->string('FOTO_SIM')->nullable();
            $table->string('FOTO_KK')->nullable();
            $table->string('FOTO_BPJS_KESEHATAN')->nullable();
            $table->string('FOTO_BPJS_KETENAGAKERJAAN')->nullable();
            $table->string('FOTO_KARYAWAN')->nullable();
            $table->string('FOTO_KONTRAK_KERJA')->nullable();

            $table->rememberToken();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('KODE_LOKASI')->references('KODE')->on('warehouses')->onDelete('restrict');
            $table->foreign('KODE_JABATAN')->references('KODE')->on('positions')->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('staffs');
    }
};
