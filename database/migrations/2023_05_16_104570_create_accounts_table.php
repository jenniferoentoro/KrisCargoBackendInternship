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
        Schema::create('accounts', function (Blueprint $table) {
            $table->string("KODE")->primary();
            $table->string('NAMA_ACCOUNT');
            $table->string('INDUK')->nullable();
            $table->unsignedBigInteger('DETIL');
            $table->string('KODE_COST_GROUP');
            $table->string('KETERANGAN');
            // soft delete
            $table->softDeletes();


            $table->foreign('KODE_COST_GROUP')->references('KODE')->on('cost_groups')->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('accounts');
    }
};
