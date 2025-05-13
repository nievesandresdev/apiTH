<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use App\Models\User;


class AddCodeToUsersSeeder extends Seeder
{
    public function run()
    {
        User::whereNull('code')->chunkById(100, function ($users) {
            foreach ($users as $user) {
                $user->code = Str::random(12);
                $user->saveQuietly();
            }
        });

    }
}
