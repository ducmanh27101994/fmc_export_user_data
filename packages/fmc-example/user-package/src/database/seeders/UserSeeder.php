<?php

namespace FmcExample\UserPackage\database\seeders;

use Faker\Factory as Faker;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        //
        $faker = Faker::create();
        $countries = ['VIETNAM', 'USA', 'CANADA', 'AUSTRALIA', 'JAPAN'];
        $batchSize = 500;
        $totalRecords = 500000;

        for ($i = 0; $i < $totalRecords / $batchSize; $i++) {
            $users = [];
            for ($j = 0; $j < $batchSize; $j++) {
                $users[] = [
                    'name' => $faker->userName(),
                    'email' => $faker->unique()->email(),
                    'password' => $faker->password(),
                    'created_at' => $faker->dateTimeBetween('-10 years', 'now'),
                    'is_verified' => $faker->boolean,
                    'country' => $faker->randomElement($countries),
                    'birth_day' => $faker->date('Y-m-d', '2015-10-10'),
                ];
            }
            DB::table('users')->insert($users);
        }

    }
}
