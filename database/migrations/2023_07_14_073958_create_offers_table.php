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
        Schema::create('offers', function (Blueprint $table) {
            $table->string("KODE")->primary();
            $table->string("KODE_JENIS_ORDER");
            $table->date('TANGGAL');
            $table->string("RATE_STATUS");
            $table->string('KODE_CUSTOMER')->nullable();
            $table->string('NAMA_CUSTOMER')->nullable();
            $table->string('CONTACT_PERSON')->nullable();
            $table->string('EMAIL')->nullable();
            $table->string('STATUS')->nullable();
            $table->string('PPN')->nullable();
            $table->string('PPN_PERCENTAGE')->nullable();
            $table->string('PPH')->nullable();
            $table->string('PAYMENT');
            $table->string('TOP');
            $table->string('KETERANGAN_TOP');
            $table->string('KETERANGAN_TAMBAHAN')->nullable();
            $table->string('SALES');
            $table->softDeletes();
            $table->timestamps();

            $table->foreign('KODE_CUSTOMER')->references('KODE')->on('customers')->onDelete('restrict');
            $table->foreign('KODE_JENIS_ORDER')->references('KODE')->on('order_types');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('offers');
    }
};
