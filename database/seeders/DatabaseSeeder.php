<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;

use App\Models\Customer;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        // \App\Models\Staff::factory(10)->create();

        // \App\Models\Staff::factory()->create([
        //     'name' => 'Test User',
        //     'email' => 'test@example.com',
        // ]);

        $this->call([
            // PositionSeeder::class,
            UserSeeder::class,
            // CountrySeeder::class,
            // ProvinceSeeder::class,
            // CitySeeder::class,
            // LocationSeeder::class,
            // StaffSeeder::class,
            // BusinessTypeSeeder::class,
            CostGroupSeeder::class,
            AccountSeeder::class,
            // CustomerGroupSeeder::class,
            // VendorTypeSeeder::class,
            // VendorSeeder::class,
            // WarehouseSeeder::class,
            // CustomerSeeder::class,
            // HarborSeeder::class,
        ]);
        //call 20 times CustomerGroupSeeder
        // for ($i = 0; $i < 20; $i++) {
        //     $this->call(CustomerGroupSeeder::class);
        // }
    }
}
