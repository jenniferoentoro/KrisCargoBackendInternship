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
        Schema::create('ship_routes', function (Blueprint $table) {
            $table->bigIncrements("KODE");
            $table->string('RUTE');
            $table->string('KD_ASAL');
            $table->string('KD_TUJUAN');
            $table->string('HP');
            $table->string('PIC');
            $table->string('TARIF_LCL_CUS');
            $table->string('TARIF_FCL_CUS');
            $table->string('TARIF_KON_KOSONG_PORT');
            $table->string('TARIF_KON_ISI_PORT');

            $table->foreign('KD_ASAL')->references('KODE')->on('cities')->onDelete('restrict');
            $table->foreign('KD_TUJUAN')->references('KODE')->on('cities')->onDelete('restrict');

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
        Schema::dropIfExists('ship_routes');
    }
};