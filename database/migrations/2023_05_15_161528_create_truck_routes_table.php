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
        Schema::create('truck_routes', function (Blueprint $table) {
            $table->string("KODE")->primary();

            $table->string('RUTE_ASAL');
            $table->string('KD_KOTA_ASAL');
            $table->string('RUTE_TUJUAN');
            $table->string('KD_KOTA_TUJUAN');
            $table->string('KETERANGAN');

            $table->foreign('KD_KOTA_ASAL')->references('KODE')->on('cities')->onDelete('restrict');
            $table->foreign('KD_KOTA_TUJUAN')->references('KODE')->on('cities')->onDelete('restrict');

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
        Schema::dropIfExists('truck_routes');
    }
};
