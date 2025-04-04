<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Faker\Factory as Faker;

class CustomerSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $faker = Faker::create();

        // Crear 20 clientes de ejemplo
        for ($i = 0; $i < 20; $i++) {
            DB::table('customers')->insert([
                'NIT' => $faker->unique()->numberBetween(1000000, 9999999),
                'name' => $faker->company,
                'email' => $faker->unique()->companyEmail,
                'phone' => $faker->optional(0.8)->phoneNumber,
                'cellphone' => $faker->optional(0.9)->phoneNumber,
                'address' => $faker->address,
                'department' => $faker->state,
                'active' => $faker->boolean(90),
                'role_id' => 3,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
