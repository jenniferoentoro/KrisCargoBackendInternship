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
        Schema::create('customer_groups', function (Blueprint $table) {
            $table->string("KODE")->primary();
            $table->string('NAMA');
            $table->string('BADAN_HUKUM');
            $table->string('ALAMAT');
            $table->string('KODE_KOTA');
            // $table->string('KODE_PROVINSI');
            // $table->string('KODE_NEGARA');
            $table->string('TELP');
            $table->string('HP');
            $table->string('EMAIL');
            $table->string('FAX');
            $table->string('CONTACT_PERSON');
            $table->string('NO_HP_CP');
            $table->string('NO_SMS_CP');
            $table->boolean('AKTIF');
            $table->string('KETERANGAN');
            $table->string('WEBSITE');
            $table->string('EMAIL1');

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
        Schema::dropIfExists('customer_groups');
    }
};
