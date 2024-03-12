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
        Schema::create('hpp_trucks', function (Blueprint $table) {
            $table->string("KODE")->primary();
            $table->string('KODE_RUTE_TRUCK');
            // $table->unsignedBigInteger('KODE_VENDOR');
            $table->string('KODE_COMMODITY');
            $table->string("KODE_TRUCK");
            // $table->string('UK_KONTAINER');
            $table->date('BERLAKU');
            $table->unsignedBigInteger('HARGA_JUAL');
            $table->string('KETERANGAN');
            $table->string('KODE_VENDOR');


            $table->timestamps();

            $table->foreign('KODE_TRUCK')->references('KODE')->on('trucks')->onDelete('restrict');
            $table->foreign('KODE_RUTE_TRUCK')->references('KODE')->on('truck_routes')->onDelete('restrict');
            $table->foreign('KODE_COMMODITY')->references('KODE')->on('commodities')->onDelete('restrict');

            $table->foreign('KODE_VENDOR')->references('KODE')->on('vendors')->onDelete('restrict');

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
        Schema::dropIfExists('hpp_trucks');
    }
};
