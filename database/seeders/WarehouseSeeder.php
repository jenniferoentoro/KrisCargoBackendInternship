<?php

namespace Database\Seeders;


use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class WarehouseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $countries = [
            [
                'NAMA' => '-',
                'JENIS_LOKASI' => 'GUDANG',
                'ALAMAT' => '-',
                'KODE_KOTA' => 'KOT.1',
                'KETERANGAN' => '-',
                'NAMA_PIC' => '-',
                'HP_PIC' => '-',
                'EMAIL_PIC' => '-',
                'KODE' => 'LOK.1',
            ],

            // Add more countries as needed
        ];

        DB::table('warehouses')->insert($countries);
    }
}
