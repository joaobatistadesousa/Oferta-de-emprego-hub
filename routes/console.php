<?php

use Illuminate\Support\Facades\Schedule;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;
use App\Services\SendMessageReminder;

Schedule::call(function () {
    Log::info('Iniciando o cron job');

    $sendMessageReminder = new SendMessageReminder(
        "hubprolog",
        "Key cm90ZWFkb3JodWI6SWgzWUpIUkpkSWFpNklGMVR0cHE=",
        "direcionadorhub",
        "3ecc242e-6064-4b6f-8a5a-ae74c587ef19",
        "21ae837c-9c06-4136-8bdc-01c50c7adedd",
        'oferta_emprgo'
    );

    Log::info('Instância de SendMessageReminder criada');

    if ($sendMessageReminder->sendMessageReminder() === false) {
        Log::warning('sendMessageReminder retornou false. Cron job não será executado.');
        return; // Não executa o cron job se retornar false
    }

    Log::info('sendMessageReminder não retornou false. Continuando a execução do cron job.');

    // Coloque aqui o código que deve ser executado se sendMessageReminder não retornar false

})->everyMinute()->name('lembrete oferta emprgo')->timezone('America/Sao_Paulo');
