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
        Schema::create('joa_details', function (Blueprint $table) {
            $table->bigIncrements("KODE");
            $table->string('NO_JOA');
            $table->string('KODE_JENIS_BIAYA');
            $table->unsignedBigInteger('KODE_KEL_BIAYA');
            $table->unsignedBigInteger('KODE_BIAYA');
            $table->string('NAMA_BIAYA');
            $table->string('TARIF');
            $table->string('KETERANGAN');


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
        Schema::dropIfExists('joa_details');
    }
};
