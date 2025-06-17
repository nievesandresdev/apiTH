<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Api\Metadata\MetadataController;

/*NO debe tener middlewares para poder que funcioone con el servidor de RRSS */
Route::prefix('metadata')->group(function () {
    Route::get('/{subdomain}', [MetadataController::class, 'show']);
});
