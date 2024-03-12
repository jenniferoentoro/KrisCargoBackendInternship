<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CustomerGroupSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $companies = [
            [

                'KODE' => 'GM.NONE',
                'NAMA' => 'NONE',
                'BADAN_HUKUM' => '-',
                'ALAMAT' => '-',
                'KODE_KOTA' => 'KOT.1',
                // 'KODE_PROVINSI' => 1,
                // 'KODE_NEGARA' => 1,
                'TELP' => '-',
                'HP' => '-',
                'EMAIL' => '-',
                'FAX' => '-',
                'CONTACT_PERSON' => '-',
                'NO_HP_CP' => '-',
                'NO_SMS_CP' => '-',
                'KETERANGAN' => '-',
                'WEBSITE' => '-',
                'EMAIL1' => '-',
                'AKTIF' => 1

            ]
            // Add more companies as needed
        ];

        DB::table('customer_groups')->insert($companies);
    }
}
