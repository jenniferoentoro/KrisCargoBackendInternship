<?php

namespace Database\Seeders;


use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class VendorTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $vendors = [
            ['KODE' => 'JV.1', 'NAMA' => 'UMUM'],
            ['KODE' => 'JV.2', 'NAMA' => 'KCB'],
            ['KODE' => 'JV.3', 'NAMA' => 'PELAYARAN'],
            ['KODE' => 'JV.4', 'NAMA' => 'FORWARDING']
        ];

        DB::table('vendor_types')->insert($vendors);
    }
}
