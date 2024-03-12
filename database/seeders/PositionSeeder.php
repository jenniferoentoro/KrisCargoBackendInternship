<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PositionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $positions = [
            [
                'KODE' => 'JBT.1',
                'NAMA' => 'ADMINISTRATOR',
                'KETERANGAN' => 'Description for Position 1',
            ],
            [
                'KODE' => 'JBT.2',

                'NAMA' => 'MARKETING',
                'KETERANGAN' => 'Description for Position 2',
            ],
            [
                'KODE' => 'JBT.3',
                'NAMA' => 'OPERATIONAL',
                'KETERANGAN' => 'Description for Position 3',
            ],
            [
                'KODE' => 'JBT.4',
                'NAMA' => 'ADMINISTRASI',
                'KETERANGAN' => 'Description for Position 4',
            ],
            [
                'KODE' => 'JBT.5',
                'NAMA' => 'ACCOUNTING',
                'KETERANGAN' => 'Description for Position 5',
            ],
            [
                'KODE' => 'JBT.6',
                'NAMA' => 'SALES',
                'KETERANGAN' => 'Description for Position 6',
            ],
            [
                'KODE' => 'JBT.7',
                'NAMA' => 'AR',
                'KETERANGAN' => 'Description for Position 7',
            ],
            [
                'KODE' => 'JBT.8',
                'NAMA' => 'AP',
                'KETERANGAN' => 'Description for Position 8',
            ]

            // Add more positions as needed
        ];

        DB::table('positions')->insert($positions);
    }
}
