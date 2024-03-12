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
        Schema::create('cost_rates', function (Blueprint $table) {
            $table->string("KODE")->primary();
            $table->string('KODE_BIAYA');
            $table->string('KODE_VENDOR');
            $table->string('KODE_PELABUHAN_ASAL')->nullable();
            $table->string('KODE_PELABUHAN_TUJUAN')->nullable();
            $table->string('KODE_COMMODITY')->nullable();
            $table->string('UK_KONTAINER')->nullable();
            $table->unsignedBigInteger('TARIF');
            $table->date('TGL_BERLAKU');
            $table->string('KETERANGAN');
            $table->string('KODE_CUSTOMER');
            $table->foreign('KODE_BIAYA')->references('KODE')->on('costs')->onDelete('restrict');
            $table->foreign('KODE_VENDOR')->references('KODE')->on('vendors')->onDelete('restrict');
            $table->foreign('KODE_PELABUHAN_ASAL')->references('KODE')->on('harbors')->onDelete('restrict');
            $table->foreign('KODE_PELABUHAN_TUJUAN')->references('KODE')->on('harbors')->onDelete('restrict');
            $table->foreign('KODE_COMMODITY')->references('KODE')->on('commodities')->onDelete('restrict');
            $table->foreign('UK_KONTAINER')->references('KODE')->on('sizes')->onDelete('restrict');
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
        Schema::dropIfExists('cost_rates');
    }
};
