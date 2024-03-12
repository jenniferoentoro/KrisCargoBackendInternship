<?php

namespace Database\Seeders;

use App\Models\BusinessType;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class BusinessTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        BusinessType::create(['KODE' => 'JU.1', 'NAMA' => 'TOKO']);
        BusinessType::create(['KODE' => 'JU.2', 'NAMA' => 'DISTRIBUTOR']);
        BusinessType::create(['KODE' => 'JU.3', 'NAMA' => 'EXPEDISI']);
        BusinessType::create(['KODE' => 'JU.4', 'NAMA' => 'PABRIK']);
        BusinessType::create(['KODE' => 'JU.5', 'NAMA' => 'PUPUK']);
        BusinessType::create(['KODE' => 'JU.6', 'NAMA' => 'SEMEN']);
        BusinessType::create(['KODE' => 'JU.7', 'NAMA' => 'LCL']);
        BusinessType::create(['KODE' => 'JU.8', 'NAMA' => 'LAIN-LAIN']);
    }
}
