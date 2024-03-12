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
        Schema::create('harbors', function (Blueprint $table) {
            $table->string("KODE")->primary();
            $table->string('NAMA_PELABUHAN');
            $table->string('KODE_KOTA');
            $table->string('KETERANGAN')->nullable();
            $table->foreign('KODE_KOTA')->references('KODE')->on('cities')->onDelete('restrict');
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
        Schema::dropIfExists('harbors');
    }
};
