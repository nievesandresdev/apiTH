<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use App\Services\Hoster\Users\UserServices;

class TrackingNotification extends Command
{
    protected $userServices;
    protected $notificationFiltersInformGeneral;
    protected $notificationFiltersInformDiscontent;
    protected $specificChannels;
    public function __construct(UserServices $userServices)
    {
        parent::__construct();
        $this->userServices = $userServices;

        $this->notificationFiltersInformGeneral = [
            'informGeneral' => true,
        ];

        $this->notificationFiltersInformDiscontent = [
            'informDiscontent' => true
        ];

        $this->specificChannels = ['email'];
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


        //filtros de notificaciones

        // el 1 significa que buscara los usuarios con informe general mensual
         $this->getUsersInformGeneral($this->notificationFiltersInformGeneral, $this->specificChannels, 1); // el 1 significa mensual
         $this->getUsersInformDiscontent($this->notificationFiltersInformDiscontent, $this->specificChannels);


    }

    /**
     * semana
     */
    protected function weeklyTracking()
    {
        Log::info('inicia cron semana');


        $this->getUsersInformGeneral($this->notificationFiltersInformGeneral, $this->specificChannels, 2); // el 2 significa semanal
        $this->getUsersInformDiscontent($this->notificationFiltersInformDiscontent, $this->specificChannels);
    }



    protected function getUsersInformGeneral($notificationFilters, $specificChannels, $periodicity) //para mandar el email
    {
        try {
            Log::info('Iniciando getUsersInformGeneral con periodicity: ' . $periodicity);
            $usersByChannel = $this->userServices->getUsersWithNotifications($notificationFilters, $specificChannels, $periodicity);


            foreach ($usersByChannel as $channel => $users) {

                foreach ($users as $user) {
                    $hotelIds = $user->hotel->pluck('id')->implode(', ');
                    Log::info('Usuario encontrado - Email: ' . $user->email . ' - Hoteles: ' . $hotelIds);
                }
            }
        } catch (\Exception $e) {
            Log::error('Error en getUsersInformGeneral: ' . $e->getMessage());
            Log::error('Stack trace: ' . $e->getTraceAsString());
        }
    }

    protected function getUsersInformDiscontent($notificationFilters, $specificChannels) //para mandar el email
    {
        try {
            Log::info('Iniciando getUsersInformDiscontent');
            $usersByChannel = $this->userServices->getUsersWithNotifications($notificationFilters, $specificChannels);

            foreach ($usersByChannel as $channel => $users) {

                foreach ($users as $user) {
                    $hotelIds = $user->hotel->pluck('id')->implode(', ');
                    Log::info('Usuario encontrado - Email: ' . $user->email . ' - Hoteles: ' . $hotelIds);
                }
            }
        } catch (\Exception $e) {
            Log::error('Error en getUsersInformDiscontent: ' . $e->getMessage());
            Log::error('Stack trace: ' . $e->getTraceAsString());
        }
    }
}
