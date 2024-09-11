<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Event;
use App\Models\Worker;
use App\Models\WorkersEvent;
use App\Services\SendMessageBid;
use App\Services\EventWorkService;

class EventWorkController extends Controller
{
 // app/Http/Controllers/EventController.php

public function storeOrUpdate(Request $request)
{
    // Validar dados
    $validatedData = $request->validate([
        'idevento' => 'required|integer',
        'evento' => 'required|string|max:255',
        'data' => 'required|date_format:d/m/Y',
        'hora' => 'required|date_format:H:i:s',
        'contato' => 'nullable|string|max:255',
        'valor' => 'nullable|numeric',
        'endereco' => 'nullable|string',
        'workers' => 'required|array',
        'workers.*.id' => 'required|string',
        'workers.*.nome' => 'required|string|max:255',
        'workers.*.telefone' => 'required|string|max:20',
    ]);

    // Converter a data para o formato do banco de dados
    $validatedData['data'] = \Carbon\Carbon::createFromFormat('d/m/Y', $validatedData['data'])->format('Y-m-d');

    // Inserir ou atualizar o evento
    $event = Event::updateOrCreate(
        ['idevento' => $validatedData['idevento']],
        [
            'evento' => $validatedData['evento'],
            'data' => $validatedData['data'],
            'hora' => $validatedData['hora'],
            'contato' => $validatedData['contato'],
            'valor' => $validatedData['valor'],
            'endereco' => $validatedData['endereco']
        ]
    );

    // Associar trabalhadores ao evento
    foreach ($validatedData['workers'] as $workerData) {
        // Remover o sinal de mais (+) do telefone
        $telefoneSemMais = str_replace('+', '', $workerData['telefone']);

        // Formatar o contact_identity
        $contactIdentity = $telefoneSemMais . "@wa.gw.msging.net";

        $worker = Worker::updateOrCreate(
            ['id_work' => $workerData['id']],
            [
                'contact_identity' => $contactIdentity,
                'nome' => $workerData['nome'],
                'telefone' => $workerData['telefone'],
            ]
        );

        // Inserir ou atualizar a relação na tabela pivot
        WorkersEvent::updateOrCreate(
            [
                'worker_id' => $worker->id,
                'idevento' => $event->idevento
            ],
            [
                'aceitou' => false,
                'triggerMessageOferta' => false,
                'triggerMessageLembrete' => false,
            ]
        );
    }

    $sendMessageBidService = new SendMessageBid(
        "hubprolog",
        "Key cm90ZWFkb3JodWI6SWgzWUpIUkpkSWFpNklGMVR0cHE=",
        "direcionadorhub",
        "3ecc242e-6064-4b6f-8a5a-ae74c587ef19",
        "21ae837c-9c06-4136-8bdc-01c50c7adedd",
        'oferta_emprgo'
    );

    // Obtém o idevento do corpo da requisição


    // Chame o método getWorkers e retorne os resultados
    $result = $sendMessageBidService->sendMessageBid($request->input('idevento'));

    return response()->json([
        'message' => 'Eventos e trabalhadores criados/atualizados com sucesso',
    ]);




}

public function updateIsAceptOferta( Request $request) {
    $EventWorkService= new EventWorkService();
    response()->json("Eventos e trabalhadores criados/atualizados com sucesso updateIsAceptOferta");
    $EventWorkService->updateIsAceptOferta($request->input('idevento'), $request->input('contact_identity'), $request->input('choiceOption'));

}
public function getAllWorkersAndEventsWenAcceptisTrue(Request $request)

{

    $EventWorkService= new EventWorkService();
    return $EventWorkService->getWorkwhatIsAccepted();

}


public function getAllWorkersAndEventsWenAcceptisFalse(Request $request)

{

    $EventWorkService= new EventWorkService();
    return $EventWorkService->getWorkwhatNotAccepted();

}
public function deleteWorkerEvent(Request $request)
{
    // Verifica o conteúdo do worker_id no request

    // Instancia o serviço para manipulação
    $EventWorkService = new EventWorkService();

    // Pega os valores diretamente do request
    $eventId = $request->input('idevento');
    $workerIds = $request->input('worker_id'); // Verifica se está retornando um array

    // Verifica se worker_id foi passado corretamente
    if (is_null($workerIds) || !is_array($workerIds)) {
        return response()->json(['error' => 'worker_id deve ser um array'], 400);
    }

    // Chama a função de deleção
    return $EventWorkService->deleteWorkerEvent($eventId, $workerIds);
}

}
