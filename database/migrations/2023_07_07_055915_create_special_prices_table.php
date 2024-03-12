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
        # HARGA LCL
        Schema::create('special_prices', function (Blueprint $table) {
            $table->string("KODE")->primary();
            // $table->string('NAMA_HARGA_KHUSUS');
            $table->string('KODE_PRODUK');
            $table->string('KODE_POL');
            $table->string('KODE_POD');
            // $table->unsignedBigInteger('KODE_RUMUS');
            $table->unsignedBigInteger('HARGA_JUAL');
            $table->date('BERLAKU');
            $table->string('KODE_CUSTOMER');
            $table->softDeletes();
            $table->timestamps();

            $table->foreign('KODE_PRODUK')->references('KODE')->on('products');
            $table->foreign('KODE_POL')->references('KODE')->on('harbors');
            $table->foreign('KODE_POD')->references('KODE')->on('harbors');
            // $table->foreign('KODE_RUMUS')->references('KODE')->on('formulas');
            $table->foreign('KODE_CUSTOMER')->references('KODE')->on('customers');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('special_prices');
    }
};
