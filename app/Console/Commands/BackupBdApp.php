<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;

class BackupBdApp extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'backup:bd-app';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Realiza los backups de las bases de datos principales y helpers';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        // Ejecutar el backup de la base de datos principal (hoster)
        $this->info('Iniciando backup de la base de datos hoster...');
        Artisan::call('backup:hoster');
        $this->info(Artisan::output());

        // Ejecutar el backup de la base de datos (helpers)
        $this->info('Iniciando backup de la base de datos helpers...');
        Artisan::call('backup:helpers');
        $this->info(Artisan::output());

        $this->info('Backups completados.');
    }
}
