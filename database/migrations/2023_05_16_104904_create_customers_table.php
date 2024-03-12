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
        Schema::create('customers', function (Blueprint $table) {
            $table->string("KODE")->primary();
            $table->string('NAMA');
            $table->string('BADAN_HUKUM');
            $table->string('KODE_GROUP');

            $table->string('NO_KTP');
            $table->string('NAMA_KTP');
            $table->string('ALAMAT_KTP', 1000);
            $table->string('RT_KTP');
            $table->string('RW_KTP');
            $table->string('KELURAHAN_KTP');
            $table->string('KECAMATAN_KTP');
            $table->string('KODE_KOTA_KTP');
            $table->string('JENIS');
            $table->string('NAMA_NPWP');
            $table->string('NO_NPWP');
            $table->string('ALAMAT_NPWP', 1000);
            $table->string('RT_NPWP');
            $table->string('RW_NPWP');
            $table->string('KELURAHAN_NPWP');
            $table->string('KECAMATAN_NPWP');
            $table->string('KODE_KOTA_NPWP');
            $table->string('CONTACT_PERSON_1');
            $table->string('JABATAN_1');
            $table->string('NO_HP_1');
            $table->string('EMAIL_1');
            $table->string('CONTACT_PERSON_2')->nullable();
            $table->string('JABATAN_2')->nullable();
            $table->string('NO_HP_2')->nullable();
            $table->string('EMAIL_2')->nullable();
            $table->string('DIBAYAR');
            $table->string('LOKASI');
            $table->unsignedBigInteger('TOP');
            $table->string('PAYMENT');
            $table->string('KETERANGAN_TOP');
            $table->string('TELP');
            $table->string('HP');
            $table->string('WEBSITE');
            $table->string('EMAIL');
            $table->unsignedBigInteger('KODE_AR');
            $table->unsignedBigInteger('KODE_SALES');
            $table->date('TGL_REG')->nullable();
            $table->string('FOTO_KTP')->nullable();
            $table->string('FOTO_NPWP')->nullable();
            $table->string('FORM_CUSTOMER')->nullable();
            $table->string('KODE_USAHA');
            $table->unsignedBigInteger('PLAFON');

            $table->foreign('KODE_GROUP')->references('KODE')->on('customer_groups')->onDelete('restrict');
            $table->foreign('KODE_KOTA_KTP')->references('KODE')->on('cities')->onDelete('restrict');
            $table->foreign('KODE_KOTA_NPWP')->references('KODE')->on('cities')->onDelete('restrict');
            $table->foreign('KODE_USAHA')->references('KODE')->on('business_types')->onDelete('restrict');
            $table->foreign('KODE_SALES')->references('KODE')->on('staffs')->onDelete('restrict');
            $table->foreign('KODE_AR')->references('KODE')->on('staffs')->onDelete('restrict');



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
        Schema::dropIfExists('customers');
    }
};
