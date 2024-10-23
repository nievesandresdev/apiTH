<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use App\Models\Backup;
use Illuminate\Support\Facades\File;
use Carbon\Carbon;

class BackupHoster extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'backup:hoster';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Backup de la base de datos general';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $sixMonthsAgo = Carbon::now()->subMonths(6);
        $storage = 's3';

        // Obtener el primer backup registrado en la base de datos (el más antiguo)
        $firstBackup = Backup::where('type', 'hoster')->oldest()->first();

        if ($firstBackup && $firstBackup->created_at < $sixMonthsAgo) {
            if (Storage::disk($storage)->exists($firstBackup->file_path)) {
                Storage::disk($storage)->delete($firstBackup->file_path);
                Log::info('El primer backup (de hace más de 6 meses) ha sido eliminado correctamente.');
            } else {
                Log::info('No se encontró el archivo de backup antiguo para eliminar.');
            }

            $firstBackup->delete();
        }

        $uniqueFileName = now()->format('Y-m-d-H-i-s') . '-hoster.zip';
        $folderPath = 'hoster-backup-db';

        // Generar el nombre del archivo SQL
        $sqlFilename = 'hoster_' . now()->format('Y-m-d-H-i-s') . '.sql';

        // Ruta  donde se guardará el archivo SQL
        $sqlFilePath = storage_path('app/' . $sqlFilename);

        // Comando para realizar el dump de la base de datos
        $command = sprintf(
            'mysqldump --user=%s --password=%s --host=%s %s > %s',
            env('DB_USERNAME'),
            env('DB_PASSWORD'),
            env('DB_HOST'),
            env('DB_DATABASE'),
            $sqlFilePath
        );

        // Ejecutar el comando
        shell_exec($command);

        // Verificar si el archivo SQL fue generado
        if (file_exists($sqlFilePath)) {
            $zipFilePath = storage_path('app/' . $uniqueFileName);
            $zip = new \ZipArchive();

            if ($zip->open($zipFilePath, \ZipArchive::CREATE) === TRUE) {
                // Añadir el archivo SQL al ZIP
                $zip->addFile($sqlFilePath, $sqlFilename);
                $zip->close();

                // Subir el archivo ZIP a S3
                $s3Path = $folderPath . '/' . $uniqueFileName;
                Storage::disk($storage)->put($s3Path, file_get_contents($zipFilePath));

                // Eliminar el archivo SQL y el archivo ZIP local
                File::delete($sqlFilePath);
                File::delete($zipFilePath);

                // Registrar el backup en la base de datos
                Backup::create([
                    'file_name' => $uniqueFileName,
                    'file_path' => $s3Path,
                    'disk' => $storage,
                    'type' => 'hoster',
                ]);

                Log::info('Backup de la base de datos principal (general) completado y guardado.');
            } else {
                Log::error('Error al crear el archivo .zip.');
            }
        } else {
            Log::error('Error al crear el dump de la base de datos.');
        }
    }
}
