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
        Schema::create('ships', function (Blueprint $table) {
            $table->string("KODE")->primary();
            $table->string('KAPAL');
            $table->string('KODE_VENDOR');
            $table->string('KETERANGAN');
            $table->softDeletes();
            $table->timestamps();


            $table->foreign('KODE_VENDOR')->references('KODE')->on('vendors')->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('ships');
    }
};
