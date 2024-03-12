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
        Schema::create('offer_details', function (Blueprint $table) {
            $table->string("KODE")->primary();

            $table->string('KODE_PRAJOA')->nullable();

            $table->string('KODE_POL')->nullable();
            $table->string('KODE_POD')->nullable();
            $table->string('KODE_DOOR_POL')->nullable();
            $table->string('KODE_DOOR_POD')->nullable();
            $table->string('KODE_COMMODITY');
            $table->string('KODE_SERVICE');
            $table->string('KODE_UK_KONTAINER')->nullable();
            $table->string('KODE_JENIS_CONTAINER')->nullable();
            $table->string('STUFFING')->nullable();
            $table->string('STRIPPING')->nullable();
            $table->string('BURUH_MUAT')->nullable();
            $table->string('BURUH_MUAT_KET')->nullable();
            $table->string('BURUH_SALIN')->nullable();
            $table->string('BURUH_SALIN_KET')->nullable();
            $table->string('BURUH_BONGKAR')->nullable();

            $table->string('ASURANSI');
            $table->string('TSI')->nullable();
            $table->string('TSI_NOMINAL')->nullable();
            $table->string('FREE_TIME_STORAGE');
            $table->string('FREE_TIME_DEMURRAGE');
            $table->string('HARGA');
            $table->string('SATUAN_HARGA')->nullable();


            $table->string('KODE_PENAWARAN');

            $table->softDeletes();
            $table->timestamps();


            $table->foreign('KODE_PENAWARAN')->references('KODE')->on('offers');
            $table->foreign('KODE_POL')->references('KODE')->on('harbors');
            $table->foreign('KODE_POD')->references('KODE')->on('harbors');
            $table->foreign('KODE_UK_KONTAINER')->references('KODE')->on('sizes');
            $table->foreign('KODE_JENIS_CONTAINER')->references('KODE')->on('container_types');

            $table->foreign('KODE_COMMODITY')->references('KODE')->on('commodities');
            $table->foreign('KODE_SERVICE')->references('KODE')->on('services');
            // $table->foreign('KODE_PRAJOA')->references('KODE')->on('pra_joas')->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('offer_details');
    }
};
