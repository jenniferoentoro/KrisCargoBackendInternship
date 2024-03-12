<?php

namespace Database\Seeders;

use App\Models\Harbor;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class HarborSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Harbor::create(['KODE' => 'ADN', 'NAMA_PELABUHAN' => 'ADONARA', 'KODE_KOTA' => 'KOT.1', 'KETERANGAN' => 'Sebuah pelabuhan...']);
        Harbor::create(['KODE' => 'AMB', 'NAMA_PELABUHAN' => 'AMBON', 'KODE_KOTA' => 'KOT.1', 'KETERANGAN' => 'Sebuah pelabuhan...']);
    }
}
