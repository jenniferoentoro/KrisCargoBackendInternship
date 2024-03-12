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
        Schema::create('joa_heads', function (Blueprint $table) {
            $table->bigIncrements("KODE");
            $table->date('TGL');
            $table->date('BERLAKU');
            $table->date('EXP');
            $table->unsignedBigInteger('KD_CUST');
            $table->string('NM_CUST');
            $table->string('ALAMAT_PENGIRIMAN');
            $table->string('KOTA_PENGIRIMAN');
            $table->string('PROP_PENGIRIMAN');
            $table->string('NEGA_PENGIRIMAN');
            $table->string('KD_SALES');
            $table->string('NM_SALES');
            $table->string('KD_RUTE_PENGIRIMAN'); //ini ga tau kode apa
            $table->string('KD_RUTE_KAPAL');
            $table->string('POL');
            $table->string('POD');
            $table->string('UK');
            $table->string('JENIS_CON');
            $table->string('JENIS_ORDER');
            $table->string('COMMODITY');
            $table->string('SERVICE');
            $table->string('KD_RUTE_TRUCK_POL');
            $table->string('KD_RUTE_TRUCK_POD');
            $table->string('TOP');
            $table->string('HARGA_KONTAINER');
            $table->string('HARGA_TRUCK_POL');
            $table->string('HARGA_TRUCK_POD');
            $table->string('BI_DOKUMEN');
            $table->string('BI_ASURANSI');
            $table->string('SUB_TOTAL');
            $table->string('PPN');
            $table->string('PPN_PERSEN');
            $table->string('GRAND_TOTAL');
            $table->string('KETERANGAN');

            // $table->foreign('KD_CUST')->references('KODE')->on('customers')->onDelete('restrict');
            // $table->foreign('KD_SALES')->references('KODE')->on('sales')->onDelete('restrict');
            // $table->foreign('KD_RUTE_KAPAL')->references('KODE')->on('ship_routes')->onDelete('restrict');
            // $table->foreign('KD_RUTE_TRUCK_POL')->references('KODE')->on('truck_routes')->onDelete('restrict');
            // $table->foreign('KD_RUTE_TRUCK_POD')->references('KODE')->on('truck_routes')->onDelete('restrict');

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
        Schema::dropIfExists('joa_heads');
    }
};
