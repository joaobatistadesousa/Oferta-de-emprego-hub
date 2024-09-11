<?php
namespace App\Services;


use App\Models\Worker;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Models\Event;

class EventWorkService
{
    public function updateTriggerMessageOfertaDisparo($eventId, $contact_identity)
    {
        // Busca o trabalhador pelo contact_identity
        $worker = DB::table('workers')
            ->where('contact_identity', $contact_identity)
            ->first();

        if ($worker) {
            // Atualiza o campo triggerMessageOferta na tabela workers_event
            $updated = DB::table('workers_event')
                ->where('idevento', $eventId)
                ->where('worker_id', $worker->id)
                ->update(['triggerMessageOferta' => 1]);

            return $updated > 0; // Retorna true se pelo menos um registro foi atualizado
        }

        return false; // Caso o trabalhador não seja encontrado
    }


    function updateIsAceptOferta($eventId, $contact_identity,$choiceOption)
    {
        // Busca o trabalhador pelo contact_identity
        $worker = DB::table('workers')
            ->where('contact_identity', $contact_identity)
            ->first();

        if ($worker) {
            // Atualiza o campo isAceptOferta na tabela workers_event
            $updated = DB::table('workers_event')
                ->where('idevento', $eventId)
                ->where('worker_id', $worker->id)
                ->update(['aceitou' => $choiceOption]);
        }
    }
    public function getWorkwhatIsAccepted()
{
    // Realiza um join entre 'workers_event' e 'workers'
    $acceptedWorkers = DB::table('workers_event')
        ->join('workers', 'workers_event.worker_id', '=', 'workers.id') // Faz o join usando o worker_id e id
        ->where('workers_event.aceitou', 1) // Filtra os trabalhadores que aceitaram
        ->where('workers_event.triggerMessageOferta', 1) // Adiciona a condição para triggerMessageOferta
        ->select('workers.id_work', 'workers_event.idevento') // Seleciona os campos desejados
        ->get();

    return response()->json($acceptedWorkers);  // Retorna o resultado como JSON
}

public function getWorkwhatNotAccepted()
{
    // Realiza um join entre 'workers_event' e 'workers'
    $notAcceptedWorkers = DB::table('workers_event')
        ->join('workers', 'workers_event.worker_id', '=', 'workers.id') // Faz o join usando o worker_id e id
        ->where('workers_event.aceitou', 0) // Filtra os trabalhadores que não aceitaram
        ->select('workers.id_work', 'workers_event.idevento') // Seleciona os campos desejados
        ->get();

    return response()->json($notAcceptedWorkers);  // Retorna o resultado como JSON
}

public function deleteWorkerEvent($eventId, array $workerIds)
{
    // Buscar IDs dos trabalhadores com base no id_work fornecido
    $workers = Worker::whereIn('id_work', $workerIds)->pluck('id');

    // Verificar se algum trabalhador foi encontrado
    if ($workers->isEmpty()) {
        return response()->json(['message' => 'Nenhum trabalhador encontrado.'], 404);
    }

    // Remove os registros da tabela workers_event para todos os workerIds encontrados
    $deletedWorkersEvent = DB::table('workers_event')
        ->where('idevento', $eventId)
        ->whereIn('worker_id', $workers) // Deleta para vários workerIds encontrados
        ->delete();

    // Remove os registros da tabela workers para os IDs encontrados
    $deletedWorkers = DB::table('workers')
        ->whereIn('id', $workers) // Deleta para vários IDs encontrados
        ->delete();

    return [
        'deleted_from_workers_event' => $deletedWorkersEvent,
        'deleted_from_workers' => $deletedWorkers
    ];
}

// public function getEventsInThreeDays() {
//     // Get the date exactly three days from now
//     $threeDaysLater = Carbon::now()->addDays(3);

//     // Query to get events that occur exactly on that date
//     $events = DB::table('events')
//                 ->whereDate('data', $threeDaysLater) // Filter by exact date
//                 ->get();

//     if ($events->isEmpty()) {
//         return response()->json([
//             'message' => 'Nenhum evento encontrado para daqui a 3 dias.',
//         ]);
//     }

//     // If events are found, return them in the response
//     return response()->json([
//         'events' => $events,
//     ]);
// }


    public function getEventsInThreeDays() {
        // Get the date exactly three days from now
        $threeDaysLater = Carbon::now()->addDays(3)->toDateString();

        // Query to get events and workers who accepted the event for exactly three days from now
        $events = Event::with(['workers' => function ($query) {
                        $query->wherePivot('aceitou', true)->
                        wherePivot("triggerMessageOferta", true)->wherePivot("triggerMessageLembrete",false);
                        // Only workers who accepted
                    }])
                    ->whereDate('data', $threeDaysLater) // Filter by the event date
                    ->get();

        if ($events->isEmpty()) {
            return response()->json([
                'message' => 'Nenhum evento encontrado para daqui a 3 dias.',
            ]);
        }

        // Return the events along with workers who accepted
        return response()->json([
            'events' => $events
        ]);
    }
    public function updateTriggerMessageLembrete($eventId, $contact_identity){


        // Busca o trabalhador pelo contact_identity
        $worker = DB::table('workers')
            ->where('contact_identity', $contact_identity)
            ->first();

        if ($worker) {
            // Atualiza o campo triggerMessageOferta na tabela workers_event
            $updated = DB::table('workers_event')
                ->where('idevento', $eventId)
                ->where('worker_id', $worker->id)
                ->update(['triggerMessageLembrete' => 1]);

            return $updated > 0; // Retorna true se pelo menos um registro foi atualizado
        }

        return false; // Caso o trabalhador não seja encontrado
    }
}


