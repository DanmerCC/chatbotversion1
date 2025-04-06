<?php

use App\Http\Controllers\ChatController;
use App\Http\Controllers\ChatControllerV2;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::middleware(['session','queue-cookies'])
    ->group(function () {
        Route::get('/chat', [ChatController::class,'index'])->name('chat.index')
            ->defaults('description', 'Listar los mensajes de la conversación actual que se encuentra en la cookie de sesión.');
        Route::post('/chat', [ChatController::class,'store'])->name('chat.store')
            ->defaults('description', 'Enviar un mensaje al modelo y recibir una respuesta. El mensaje se guarda en la base de datos y se devuelve la respuesta del modelo.');
    });


Route::prefix('v2')->group(function () {
    Route::get('/session', [ChatControllerV2::class,'getSessionId'])
        ->name('v2.chat.getSessionId')
        ->defaults('description', 'Crear una nueva sesión y devolver el ID de la sesión. Este ID se guarda en una cookie llamada session_v2_id.')
        //rate limit by minute
        ->middleware('throttle:60,1');
    Route::get('/chat', [ChatControllerV2::class,'index'])->name('v2.chat.index')->defaults('description', 'Listar los mensajes de la conversación actual que se encuentra en la cookie de sesión.');
    Route::post('/chat', [ChatControllerV2::class,'store'])->name('v2.chat.getSessionId')->defaults('description', 'Enviar un mensaje al modelo y recibir una respuesta. El mensaje se guarda en la base de datos y se devuelve la respuesta del modelo.');
})->name('chat.v2.');

