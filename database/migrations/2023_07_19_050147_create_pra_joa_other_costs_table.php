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
        Schema::create('pra_joa_other_costs', function (Blueprint $table) {
            $table->bigIncrements("KODE");
            $table->string("KODE_HPP_BIAYA");
            $table->string("NOMOR_PRAJOA");

            $table->softDeletes();

            $table->foreign("NOMOR_PRAJOA")->references("NOMOR")->on("pra_joas")->onDelete("restrict");
            $table->foreign("KODE_HPP_BIAYA")->references("KODE")->on("cost_rates")->onDelete("restrict");
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('pra_joa_other_costs');
    }
};
