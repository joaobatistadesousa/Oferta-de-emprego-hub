<?php
use Illuminate\Support\Facades\Route;
use App\Services\SendMessageReminder;
// routes/api.php
use App\Http\Controllers\EventWorkController;
Route::post('/event-work', [EventWorkController::class, 'storeOrUpdate']);

Route::put('/eventos/aceite-oferta', [EventWorkController::class, 'updateIsAceptOferta']);
Route::get('/eventos/aceite-oferta', [EventWorkController::class, 'getAllWorkersAndEventsWenAcceptisTrue']);
Route::get('/eventos/aceite-nao-ofertaNao', [EventWorkController::class, 'getAllWorkersAndEventsWenAcceptisFalse']);
Route::delete('/delete/workers', [EventWorkController::class, 'deleteWorkerEvent']);
//test
Route::get('/eventos-futuros', function () {
    $SendMessageReminder= new SendMessageReminder(
        "hubprolog",
        "Key cm90ZWFkb3JodWI6SWgzWUpIUkpkSWFpNklGMVR0cHE=",
        "direcionadorhub",
        "3ecc242e-6064-4b6f-8a5a-ae74c587ef19",
        "21ae837c-9c06-4136-8bdc-01c50c7adedd",
        'oferta_emprgo'
    );;
    return $SendMessageReminder->sendMessageReminder();

});
