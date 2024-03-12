<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        User::create([
            'EMAIL' => 'admin@gmail.com',
            'PASSWORD' => bcrypt('password'),
            'NAMA' => 'Admin',
            'KODE_JABATAN' => 'JBT.1',
        ]);

        //sales@gmail.com
        User::create([
            'EMAIL' => 'sales@gmail.com',
            'PASSWORD' => bcrypt('password'),
            'NAMA' => 'Sales',
            'KODE_JABATAN' => 'JBT.6',
        ]);
    }
}
