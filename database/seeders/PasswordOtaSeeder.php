<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PasswordOtaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('password_otas')->updateOrInsert(
            ['id' => 1], // Assuming we want to update/insert the first record
            [
                'password' => 'Y3lkc2V6LTlwaWJkdS1wYUhxZXc=',
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );
    }
}
