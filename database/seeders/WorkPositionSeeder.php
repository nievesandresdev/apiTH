<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\WorkPosition;

class WorkPositionSeeder extends Seeder
{
    /**
     * Default values for periodicity_stay and periodicity_chat.
     */
    protected $defaultPeriodicityStay = [
        "pendingFeedback30" => "30",
        "pendingFeedback60" => "60",
    ];

    protected $defaultPeriodicityChat = [
        "pendingChat10" => "10",
        "pendingChat30" => "30",
    ];

    /**
     * Default notifications structure.
     */
    protected $defaultNotifications = [
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

    /**
     * Run the database seeds.
     */
    public function run()
    {
        // Obtener todas las posiciones de trabajo
        $workPositions = WorkPosition::all();

        foreach ($workPositions as $workPosition) {
            // Actualizar todas las columnas con los valores predeterminados
            $workPosition->update([
                'periodicity_stay' => json_encode($this->defaultPeriodicityStay),
                'periodicity_chat' => json_encode($this->defaultPeriodicityChat),
                'notifications' => json_encode($this->defaultNotifications),
            ]);

            $this->command->info("WorkPosition ID {$workPosition->id} actualizado con valores predeterminados.");
        }

        $this->command->info('Seeder ejecutado con éxito para todos los registros.');
    }
}
