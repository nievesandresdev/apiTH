<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use App\Models\Backup;
use Illuminate\Support\Facades\File;
use Carbon\Carbon;

class BackupHelpers extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'backup:helpers';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Backup de la base de datos helpers ';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $sixMonthsAgo = Carbon::now()->subMonths(6);
        $storage='local';

        // Obtener el primer backup registrado en la base de datos (el más antiguo)
        $firstBackup = Backup::where('type', 'helpers')->oldest()->first();

        if ($firstBackup && $firstBackup->created_at < $sixMonthsAgo) {
            // Eliminar el archivo del almacenamiento
            if (Storage::disk($storage)->exists($firstBackup->file_path)) {
                Storage::disk($storage)->delete($firstBackup->file_path);
                Log::info('El primer backup (de hace más de 6 meses) ha sido eliminado correctamente.');
            } else {
                Log::info('No se encontró el archivo de backup antiguo.');
            }

            $firstBackup->delete();
        }

        $uniqueFileName = now()->format('Y-m-d-H-i-s') . '.zip';
        $folderPath = 'helpers-backup-db';
        $sqlFilename = 'helpers_' . now()->format('Y-m-d-H-i-s') . '.sql';

        // Ruta  donde se guardará el archivo SQL
        $sqlFilePath = storage_path('app/' . $sqlFilename);

        // Comando para realizar el dump de la base de datos
        $command = sprintf(
            'mysqldump --user=%s --password=%s --host=%s %s > %s',
            env('DB_HELPERS_USERNAME'),
            env('DB_HELPERS_PASSWORD'),
            env('DB_HELPERS_HOST'),
            env('DB_HELPERS_DATABASE'),
            $sqlFilePath
        );

        // Ejecutar el comando
        shell_exec($command);

        if (file_exists($sqlFilePath)) {
            // Crear el archivo ZIP localmente
            $zipFilePath = storage_path('app/' . $uniqueFileName);
            $zip = new \ZipArchive();

            if ($zip->open($zipFilePath, \ZipArchive::CREATE) === TRUE) {
                // Añadir el archivo SQL al ZIP
                $zip->addFile($sqlFilePath, $sqlFilename);
                $zip->close();

                // Subir el archivo ZIP
                $s3Path = $folderPath . '/' . $uniqueFileName;
                Storage::disk($storage)->put($s3Path, file_get_contents($zipFilePath));

                // Eliminar el archivo SQL y zip
                File::delete($sqlFilePath);
                File::delete($zipFilePath);

                // Registrar el backup en la base de datos
                Backup::create([
                    'file_name' => $uniqueFileName,
                    'file_path' => $s3Path,
                    'disk' => $storage,
                    'type' => 'helpers',
                ]);

                Log::info('Backup de la base de datos helpers completado y guardado.');
            } else {
                Log::error('Error al crear el archivo .zip.');
            }
        } else {
            Log::error('Error al crear el dump de la base de datos helpers.');
        }
    }
}
