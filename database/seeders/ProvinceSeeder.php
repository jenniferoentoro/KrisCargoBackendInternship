<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ProvinceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $provinces = [
            ['KODE' => 'PRV.1', 'NAMA' => 'JAWA TIMUR', 'KODE_NEGARA' => 'NGR.1'],
            ['KODE' => 'PRV.2', 'NAMA' => 'JAWA BARAT', 'KODE_NEGARA' => 'NGR.1'],
            ['KODE' => 'PRV.3', 'NAMA' => 'JAWA TENGAH', 'KODE_NEGARA' => 'NGR.1'],
            // Add more provinces as needed
        ];

        DB::table('provinces')->insert($provinces);
    }
}
