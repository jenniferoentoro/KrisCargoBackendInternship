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
        Schema::create('users', function (Blueprint $table) {
            $table->bigIncrements("KODE");
            $table->string('EMAIL')->unique();
            $table->string('NAMA');
            $table->string('PASSWORD');
            $table->string('KODE_JABATAN');

            $table->rememberToken();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('KODE_JABATAN')->references('KODE')->on('positions')->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('users');
    }
};
