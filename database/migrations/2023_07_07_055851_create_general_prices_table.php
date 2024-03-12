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
        Schema::create('general_prices', function (Blueprint $table) {
            $table->bigIncrements("KODE");
            $table->string('NAMA_HARGA_UMUM');
            $table->string('KODE_PRODUK');
            $table->string('KODE_POL');
            $table->string('KODE_POD');
            $table->string('KODE_RUMUS');
            $table->string('HARGA_JUAL');
            $table->date('BERLAKU');
            $table->softDeletes();
            $table->timestamps();


            $table->foreign('KODE_PRODUK')->references('KODE')->on('products');
            $table->foreign('KODE_POL')->references('KODE')->on('harbors');
            $table->foreign('KODE_POD')->references('KODE')->on('harbors');
            $table->foreign('KODE_RUMUS')->references('KODE')->on('formulas');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('general_prices');
    }
};
