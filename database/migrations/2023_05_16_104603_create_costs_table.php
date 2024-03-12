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
        Schema::create('costs', function (Blueprint $table) {
            $table->string("KODE")->primary();
            $table->string('KD_KEL_BIAYA');
            $table->string('KD_JEN_BIAYA');
            $table->string('NAMA_BIAYA');
            $table->string('ACC');
            $table->string('KETERANGAN');

            $table->foreign('KD_KEL_BIAYA')->references('KODE')->on('cost_groups')->onDelete('restrict');
            $table->foreign('KD_JEN_BIAYA')->references('KODE')->on('cost_types')->onDelete('restrict');
            $table->foreign('ACC')->references('KODE')->on('accounts')->onDelete('restrict');

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
        Schema::dropIfExists('costs');
    }
};
