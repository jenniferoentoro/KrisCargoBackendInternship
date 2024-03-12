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
        Schema::create('products', function (Blueprint $table) {
            $table->string("KODE")->primary();
            $table->string('NAMA_PRODUK');
            $table->string('KODE_KATEGORI');
            $table->string('KODE_RUMUS');
            $table->softDeletes();
            $table->timestamps();


            $table->foreign('KODE_KATEGORI')->references('KODE')->on('categories');
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
        Schema::dropIfExists('products');
    }
};
