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
        Schema::create('pra_joas', function (Blueprint $table) {
            $table->string('NOMOR')->primary();

            $table->string('KODE_CUSTOMER');
            $table->string('KODE_VENDOR_PELAYARAN_FORWARDING');
            $table->string("KODE_POL");
            $table->string("KODE_POD");
            $table->string("KODE_UK_CONTAINER");
            $table->string("KODE_JENIS_CONTAINER");
            $table->string("KODE_JENIS_ORDER");
            $table->string("KODE_COMMODITY");
            $table->string("KODE_SERVICE");
            $table->boolean("THC_POL_INCL");
            $table->string("KODE_THC_POL")->nullable();
            $table->string("LOLO_POL_DALAM_LUAR");
            $table->boolean("LOLO_POL_INCL");
            $table->boolean("THC_POD_INCL");
            $table->string("KODE_THC_POD")->nullable();
            $table->string("LOLO_POD_DALAM_LUAR");
            $table->boolean("LOLO_POD_INCL");
            $table->string("STATUS");
            $table->boolean("REWORK_INCL");
            $table->unsignedBigInteger("NOMINAL_REWORK")->nullable();
            $table->string("KETERANGAN_REWORK")->nullable();
            $table->boolean("BURUH_MUAT_INCL");
            $table->string("KODE_HPP_BIAYA_BURUH_MUAT")->nullable();
            $table->boolean("ALAT_BERAT_POL_INCL");
            $table->unsignedBigInteger("NOMINAL_ALAT_BERAT_POL")->nullable();
            $table->string("KETERANGAN_ALAT_BERAT_POL")->nullable();
            $table->boolean("BURUH_STRIPPING_INCL");
            $table->string("KODE_HPP_BIAYA_BURUH_STRIPPING")->nullable();
            $table->boolean("BURUH_BONGKAR_INCL");
            $table->string("KODE_HPP_BIAYA_BURUH_BONGKAR")->nullable();
            $table->boolean("ALAT_BERAT_POD_STRIPPING_INCL");
            $table->unsignedBigInteger("NOMINAL_ALAT_BERAT_POD_STRIPPING")->nullable();
            $table->string("KETERANGAN_ALAT_BERAT_POD_STRIPPING")->nullable();
            $table->boolean("ALAT_BERAT_POD_BONGKAR_INCL");
            $table->unsignedBigInteger("NOMINAL_ALAT_BERAT_POD_BONGKAR")->nullable();
            $table->string("KETERANGAN_ALAT_BERAT_POD_BONGKAR")->nullable();
            $table->boolean("ASURANSI_INCL");
            $table->unsignedBigInteger("NOMINAL_TSI")->nullable();
            $table->float("PERSEN_ASURANSI")->nullable();
            $table->boolean("TRUCK_POL_INCL");
            $table->string("KODE_RUTE_TRUCK_POL")->nullable();
            $table->boolean("TRUCK_POD_INCL");
            $table->string("KODE_RUTE_TRUCK_POD")->nullable();
            $table->boolean("FEE_AGENT_POL_INCL");
            $table->unsignedBigInteger("NOMINAL_FEE_AGENT_POL")->nullable();
            $table->string("KETERANGAN_FEE_AGENT_POL")->nullable();
            $table->boolean("FEE_AGENT_POD_INCL");
            $table->unsignedBigInteger("NOMINAL_FEE_AGENT_POD")->nullable();
            $table->string("KETERANGAN_FEE_AGENT_POD")->nullable();
            $table->boolean("TOESLAG_INCL");
            $table->unsignedBigInteger("NOMINAL_TOESLAG")->nullable();
            $table->string("KETERANGAN_TOESLAG")->nullable();
            $table->boolean("SEAL_INCL");
            $table->string("KODE_HPP_BIAYA_SEAL")->nullable();
            $table->boolean("OPS_INCL");
            $table->string("KODE_HPP_BIAYA_OPS")->nullable();
            $table->boolean("KARANTINA_INCL");
            $table->unsignedBigInteger("NOMINAL_KARANTINA")->nullable();
            $table->string("KETERANGAN_KARANTINA")->nullable();
            $table->boolean("CASHBACK_INCL");
            $table->unsignedBigInteger("NOMINAL_CASHBACK")->nullable();
            $table->string("KETERANGAN_CASHBACK")->nullable();
            $table->boolean("CLAIM_INCL");
            $table->unsignedBigInteger("NOMINAL_CLAIM")->nullable();
            $table->string("KETERANGAN_CLAIM")->nullable();
            $table->boolean("BIAYA_LAIN_INCL");
            $table->boolean("BL_INCL");
            $table->unsignedBigInteger("NOMINAL_BL")->nullable();
            $table->string("KETERANGAN_BL")->nullable();
            $table->boolean("DO_INCL");
            $table->unsignedBigInteger("NOMINAL_DO")->nullable();
            $table->string("KETERANGAN_DO")->nullable();
            $table->boolean("APBS_INCL");
            $table->unsignedBigInteger("NOMINAL_APBS")->nullable();
            $table->string("KETERANGAN_APBS")->nullable();
            $table->boolean("CLEANING_INCL");
            $table->unsignedBigInteger("NOMINAL_CLEANING")->nullable();
            $table->string("KETERANGAN_CLEANING")->nullable();
            $table->boolean("DOC_INCL");
            $table->unsignedBigInteger("NOMINAL_DOC")->nullable();
            $table->string("KETERANGAN_DOC")->nullable();
            $table->unsignedBigInteger("HARGA_JUAL")->nullable();
            $table->boolean("SUDAH_DIAPPROVE")->default(false);
            $table->boolean("SUDAH_JADI_JOA")->default(false);
            //add timestamp
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('KODE_CUSTOMER')->references('KODE')->on('customers')->onDelete('restrict');
            $table->foreign('KODE_VENDOR_PELAYARAN_FORWARDING')->references('KODE')->on('vendors')->onDelete('restrict');
            $table->foreign('KODE_POL')->references('KODE')->on('harbors')->onDelete('restrict');
            $table->foreign('KODE_POD')->references('KODE')->on('harbors')->onDelete('restrict');
            // //kode ukuran container
            $table->foreign('KODE_UK_CONTAINER')->references('KODE')->on('sizes')->onDelete('restrict');
            // //jenis container
            $table->foreign('KODE_JENIS_CONTAINER')->references('KODE')->on('container_types')->onDelete('restrict');
            // //kode jenis order
            $table->foreign('KODE_JENIS_ORDER')->references('KODE')->on('order_types')->onDelete('restrict');
            // //commodity
            $table->foreign('KODE_COMMODITY')->references('KODE')->on('commodities')->onDelete('restrict');
            // //kode service
            $table->foreign('KODE_SERVICE')->references('KODE')->on('services')->onDelete('restrict');
            // //kode thc pol
            $table->foreign('KODE_THC_POL')->references('KODE')->on('thc_lolos')->onDelete('restrict');
            // //kode thc pod
            $table->foreign('KODE_THC_POD')->references('KODE')->on('thc_lolos')->onDelete('restrict');
            // //kode hpp biaya buruh muat
            $table->foreign('KODE_HPP_BIAYA_BURUH_MUAT')->references('KODE')->on('cost_rates')->onDelete('restrict');
            // //kode hpp biaya buruh stripping
            $table->foreign('KODE_HPP_BIAYA_BURUH_STRIPPING')->references('KODE')->on('cost_rates')->onDelete('restrict');
            // //kode hpp biaya buruh bongkar
            $table->foreign('KODE_HPP_BIAYA_BURUH_BONGKAR')->references('KODE')->on('cost_rates')->onDelete('restrict');
            // //kode rute truck pol
            $table->foreign('KODE_RUTE_TRUCK_POL')->references('KODE')->on('truck_routes')->onDelete('restrict');
            // //kode rute truck pod
            $table->foreign('KODE_RUTE_TRUCK_POD')->references('KODE')->on('truck_routes')->onDelete('restrict');
            // //kode hpp biaya seal
            $table->foreign('KODE_HPP_BIAYA_SEAL')->references('KODE')->on('cost_rates')->onDelete('restrict');
            // //kode hpp biaya ops
            $table->foreign('KODE_HPP_BIAYA_OPS')->references('KODE')->on('cost_rates')->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('pra_joas');
    }
};
