<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CostGroupSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $cost_groups = [
            [
                'KODE' => "C-A",
                'NAMA' => 'COST GROUP A',

            ],
            [
                'KODE' => "C-B",
                'NAMA' => 'COST GROUP B',

            ],
            // Add more account records as needed
        ];

        // Insert account records into the database
        DB::table('cost_groups')->insert($cost_groups);
    }
}
