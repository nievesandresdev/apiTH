<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Api\Metadata\MetadataController;

/*NO debe tener middlewares para poder que funcioone con el servidor de RRSS */
Route::group(['prefix' => 'metadata'], function () {
    Route::get('/{slug}', [MetadataController::class, 'show']);
});
