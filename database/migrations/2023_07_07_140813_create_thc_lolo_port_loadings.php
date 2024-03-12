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
        Schema::create('thc_lolo_port_loadings', function (Blueprint $table) {
            $table->string('KODE')->primary();
            $table->string('KODE_VENDOR');
            $table->string('KODE_PELABUHAN');
            $table->string('KODE_UK_KONTAINER');
            $table->string('KODE_JENIS_KONTAINER');
            $table->unsignedBigInteger('THC');
            $table->unsignedBigInteger('LOLO_LUAR');
            $table->unsignedBigInteger('LOLO_DALAM');
            $table->date('TGL_MULAI_BERLAKU');
            $table->date('TGL_AKHIR_BERLAKU');
            $table->softDeletes();

            $table->timestamps();

            $table->foreign('KODE_VENDOR')->references('KODE')->on('vendors')->onDelete('restrict');
            $table->foreign('KODE_PELABUHAN')->references('KODE')->on('harbors')->onDelete('restrict');
            $table->foreign('KODE_UK_KONTAINER')->references('KODE')->on('sizes')->onDelete('restrict');
            $table->foreign('KODE_JENIS_KONTAINER')->references('KODE')->on('container_types')->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('thc_lolo_port_loadings');
    }
};
