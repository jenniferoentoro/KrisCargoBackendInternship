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
        Schema::create('vendors', function (Blueprint $table) {
            $table->string('KODE')->primary();
            $table->string('NAMA');
            $table->string('KODE_JENIS_VENDOR');
            $table->string('BADAN_HUKUM');
            $table->string('STATUS');
            $table->string('NO_KTP');
            $table->string('NAMA_KTP');
            $table->string('ALAMAT_KTP');
            $table->string('RT_KTP');
            $table->string('RW_KTP');
            $table->string('KELURAHAN_KTP');
            $table->string('KECAMATAN_KTP');
            $table->string('KODE_KOTA_KTP');
            $table->string('FOTO_KTP')->nullable();
            $table->string('TELP_KANTOR');
            $table->string('HP_KANTOR');
            $table->string('WEBSITE');
            $table->string('EMAIL');
            $table->unsignedBigInteger('PLAFON');
            $table->string('NO_NPWP');
            $table->string('NAMA_NPWP');
            $table->string('ALAMAT_NPWP');
            $table->string('RT_NPWP');
            $table->string('RW_NPWP');
            $table->string('KELURAHAN_NPWP');
            $table->string('KECAMATAN_NPWP');
            $table->string('KODE_KOTA_NPWP');
            $table->string('FOTO_NPWP')->nullable();
            $table->string('CP');
            $table->string('JABATAN_CP');
            $table->string('NO_HP_CP');
            $table->string('EMAIL_CP');
            $table->string('NAMA_REKENING');
            $table->string('NO_REKENING');
            $table->string('NAMA_BANK');
            $table->string('ALAMAT_BANK');
            $table->string('TOP');
            $table->string('KETERANGAN_TOP');
            $table->date('TGL_AWAL_JADI_VENDOR');
            $table->string('PAYMENT');
            $table->string('FORM_VENDOR')->nullable();



            $table->foreign('KODE_KOTA_KTP')->references('KODE')->on('cities')->onDelete('restrict');
            $table->foreign('KODE_KOTA_NPWP')->references('KODE')->on('cities')->onDelete('restrict');
            $table->foreign('KODE_JENIS_VENDOR')->references('KODE')->on('vendor_types')->onDelete('restrict');

            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('vendors');
    }
};
