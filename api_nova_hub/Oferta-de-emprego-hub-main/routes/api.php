<?php
use Illuminate\Support\Facades\Route;
use App\Services\SendMessageReminder;
use App\Http\Middleware\CheckAuthentication;

// routes/api.php
use App\Http\Controllers\EventWorkController;
Route::post('/event-work', [EventWorkController::class, 'storeOrUpdate'])->middleware(CheckAuthentication::class);

Route::put('/eventos/aceite-oferta', [EventWorkController::class, 'updateIsAceptOferta']) ->middleware(CheckAuthentication::class);
Route::get('/eventos/aceite-oferta', [EventWorkController::class, 'getAllWorkersAndEventsWenAcceptisTrue']) ->middleware(CheckAuthentication::class);
Route::get('/eventos/aceite-nao-ofertaNao', [EventWorkController::class, 'getAllWorkersAndEventsWenAcceptisFalse']) ->middleware(CheckAuthentication::class);
Route::delete('/delete/workers', [EventWorkController::class, 'deleteWorkerEvent']) ->middleware(CheckAuthentication::class);
//test
Route::get('/eventos-futuros', function () {
    $SendMessageReminder= new SendMessageReminder(
        "hubprolog",
        "Key cm90ZWFkb3JodWI6SWgzWUpIUkpkSWFpNklGMVR0cHE=",
        "direcionadorhub",
        "3ecc242e-6064-4b6f-8a5a-ae74c587ef19",
        "21ae837c-9c06-4136-8bdc-01c50c7adedd",
        'lembreteoferta'
    );;
    return $SendMessageReminder->sendMessageReminder();



});
Route::get('/receberamMensagemEaceitaram', [EventWorkController::class, 'getAllUsersRevcivedOfertaAndAccepted']);