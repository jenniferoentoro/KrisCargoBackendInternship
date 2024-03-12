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
        Schema::create('warehouses', function (Blueprint $table) {
            $table->string("KODE")->primary();
            $table->string('NAMA');
            $table->string('JENIS_LOKASI');

            $table->string('ALAMAT');
            $table->string('KODE_KOTA');


            $table->string('KETERANGAN');

            $table->string('NAMA_PIC');
            $table->string('HP_PIC');
            $table->string('EMAIL_PIC');

            $table->softDeletes();

            $table->timestamps();


            // $table->foreign('KODE_PIC')->references('KODE')->on('users')->onDelete('restrict');
            $table->foreign('KODE_KOTA')->references('KODE')->on('cities')->onDelete('restrict');
            // $table->foreign('KODE_ACCOUNT')->references('KODE')->on('accounts')->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('warehouses');
    }
};
