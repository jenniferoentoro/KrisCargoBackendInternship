<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CitySeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $cities = [
            ['KODE' => 'KOT.1', 'NAMA' => 'SURABAYA', 'KODE_PROVINSI' => 'PRV.1'],
            ['KODE' => 'KOT.2', 'NAMA' => 'MALANG', 'KODE_PROVINSI' => 'PRV.1'],
            ['KODE' => 'KOT.3', 'NAMA' => 'MADIUN', 'KODE_PROVINSI' => 'PRV.1'],
            // Add more cities as needed
        ];

        DB::table('cities')->insert($cities);
    }
}
