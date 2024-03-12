<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AccountSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Generate account records
        $accounts = [
            [
                'KODE' => "1",
                'NAMA_ACCOUNT' => 'AKTIVA',
                'INDUK' => null,
                'DETIL' => 1,
                'KODE_COST_GROUP' => "C-A",
                'KETERANGAN' => 'DESCRIPTION 1',
            ],
            [
                'KODE' => "11000000",
                'NAMA_ACCOUNT' => 'ACCOUNT 2',
                'INDUK' => "1",
                'DETIL' => 0,
                'KODE_COST_GROUP' => "C-B",
                'KETERANGAN' => 'DESCRIPTION 2',
            ],
            // Add more account records as needed
        ];

        // Insert account records into the database
        DB::table('accounts')->insert($accounts);
    }
}
