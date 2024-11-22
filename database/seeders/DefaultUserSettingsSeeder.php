<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;

class DefaultUserSettingsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Valores por defecto
        $defaultPeriodicityStay = [
            "pendingFeedback30" => "30",
            "pendingFeedback60" => "60",
        ];

        $defaultPeriodicityChat = [
            "pendingChat10" => "10",
            "pendingChat30" => "30",
        ];

        $defaultNotifications = [
            "push" => [
                "newChat" => true,
                "newFeedback" => true,
                "new_reviews" => true,
                "pendingChat10" => true,
                "pendingChat30" => true,
                "pendingFeedback30" => true,
                "pendingFeedback60" => true,
            ],
            "email" => [
                "newChat" => false,
                "newFeedback" => false,
                "new_reviews" => false,
                "pendingChat10" => false,
                "pendingChat30" => true,
                "pendingFeedback30" => false,
                "pendingFeedback60" => false,
            ],
            "platform" => [
                "newChat" => true,
                "newFeedback" => true,
                "new_reviews" => true,
                "pendingChat10" => true,
                "pendingChat30" => true,
                "pendingFeedback30" => true,
                "pendingFeedback60" => true,
            ],
        ];

        // Iterar sobre todos los usuarios y actualizar los campos
        User::all()->each(function ($user) use ($defaultPeriodicityStay, $defaultPeriodicityChat, $defaultNotifications) {
            $user->update([
                'periodicity_stay' => json_encode($defaultPeriodicityStay),
                'periodicity_chat' => json_encode($defaultPeriodicityChat),
                'notifications' => json_encode($defaultNotifications),
            ]);
        });

        $this->command->info('Default settings added to all users.');
    }
}
