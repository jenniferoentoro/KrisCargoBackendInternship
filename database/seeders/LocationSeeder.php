<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class LocationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('warehouses')->insert([
            [
                'KODE' => 'LOK.1',
                'NAMA' => 'GUDANG DEPO',
                'JENIS_LOKASI' => 'GUDANG',
                'ALAMAT' => 'Jl. Demak',
                'KODE_KOTA' => 'KOT.1',
                'KETERANGAN' => 'GUDANG SURABAYA BERLOKASI DI DEMAK',
                'NAMA_PIC' => 'YANTO',
                'HP_PIC' => '081234567890',
                'EMAIL_PIC' => 'yanto@kriscargo.co.id',
            ],
            // Add more rows as needed
        ]);
    }
}
