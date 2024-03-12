<?php

namespace Database\Seeders;


use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CountrySeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $countries = [
            ['KODE' => 'NGR.1', 'NAMA' => 'INDONESIA'],
            ['KODE' => 'NGR.2', 'NAMA' => 'CHINA'],
            ['KODE' => 'NGR.3', 'NAMA' => 'UNITED STATES'],
            // Add more countries as needed
        ];

        DB::table('countries')->insert($countries);
    }
}
