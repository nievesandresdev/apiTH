<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use App\Services\Hoster\Users\UserServices;

class TrackingNotification extends Command
{
    protected $userServices;

    public function __construct(UserServices $userServices)
    {
        parent::__construct();
        $this->userServices = $userServices;
    }

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'tracking:notification {--month : Run monthly tracking} {--week : Run weekly tracking}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'informe de seguimiento semanal y mensual';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        if ($this->option('month')) {
            $this->monthlyTracking();
        } elseif ($this->option('week')) {
            $this->weeklyTracking();
        } else {
            $this->error('no selecciono ninguna opcion');
            return 1;
        }

        return 0;
    }

    /**
     * mes
     */
    protected function monthlyTracking()
    {
        Log::info('inicia cron mes');

        $notificationFilters = [
            'informGeneral' => true,
            'informDiscontent' => true
        ];

        $specificChannels = ['email'];

        // el 1 significa que buscara los usuarios con informe general mensual
        $usersByChannel = $this->userServices->getUsersWithNotifications($notificationFilters, $specificChannels, 1);

        foreach ($usersByChannel as $channel => $users) {
            foreach ($users as $user) {
                $hotelIds = $user->hotel->pluck('id')->implode(', '); // hoteles del usuario, puede usar su id o nombre
                Log::info('Usuario mensual: ' . $user->email . ' - Hoteles: ' . $hotelIds);
            }
        }
    }

    /**
     * semana
     */
    protected function weeklyTracking()
    {
        Log::info('inicia cron semana');

        $notificationFilters = [
            'informGeneral' => true,
            'informDiscontent' => true
        ];

        $specificChannels = ['email'];

        // el 2 significa que buscara los usuarios con informe general semanal
        $usersByChannel = $this->userServices->getUsersWithNotifications($notificationFilters, $specificChannels, 2);

        foreach ($usersByChannel as $channel => $users) {
            foreach ($users as $user) {
                $hotelIds = $user->hotel->pluck('id')->implode(', '); // hoteles del usuario, puede usar su id o nombre
                Log::info('Usuario semanal: ' . $user->email . ' - Hoteles: ' . $hotelIds);
            }
        }
    }
}
