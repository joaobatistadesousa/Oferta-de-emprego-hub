<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Event;
use App\Models\Worker;
use App\Models\WorkersEvent;
// use App\Services\SendMessageBid;
use App\Services\EventWorkService;
use Illuminate\Support\Facades\Log;
use App\Jobs\ProcessEventWorkers;
use App\Http\Requests\WorkerEventRequest;
use App\Jobs\UpdateIsAceptOfertaJob;




class EventWorkController extends Controller
{
 // app/Http/Controllers/EventController.php

 

public function storeOrUpdate(Request $request)
{
    $startTime = microtime(true);

    try {
        $validatedData = $request->validate([
            'idevento' => 'required|integer',
            'evento' => 'required|string|max:255',
            'data' => 'required|date_format:d/m/Y',
            'hora' => 'required|date_format:H:i:s',
            'contato' => 'required|string|max:255',
            'endereco' => 'required|string',
            'workers' => 'required|array',
            'workers.*.id' => 'required|string',
            'workers.*.nome' => 'required|string|max:255',
            'workers.*.telefone' => 'required|string|regex:/^\+55\d{11}$/', // Validar formato do telefone
            'workers.*.valor' => 'required|numeric',
        ]);

        // Converter a data para o formato do banco de dados
    

        // Despachar job para a fila (assíncrono)
        ProcessEventWorkers::dispatch($validatedData);
        

        // Enviar resposta imediata ao cliente
        $endTime = microtime(true);
        $executionTime = $endTime - $startTime;

        return response()->json([
            'message' => 'O processamento foi iniciado.',
            'tempo' => $executionTime . ' segundos',
        ]);

    } catch (\Illuminate\Validation\ValidationException $e) {
        // Retornar erro de validação
        return response()->json([
            'message' => 'missing parameters',
            'errors' => $e->errors(),
        ], 400);
    }
}




//atigo
// public function updateIsAceptOferta(Request $request) {
//     // Cria uma instância do serviço
//     $EventWorkService = new EventWorkService();
    
//     // Obtém os parâmetros do request
//     $idevento = $request->input('idevento');
//     $contactIdentity = $request->input('contact_identity');
//     $choiceOption = $request->input('choiceOption');
    
//     // Converte idevento para int
//     $idevento = intval($idevento);
    
//     // Verifica se choiceOption é uma string e converte corretamente para booleano
//     if ($choiceOption === "true") {
//         $choiceOption = true;
//     } elseif ($choiceOption === "false") {
//         $choiceOption = false;
//     }
    
//     // Chama o método de atualização no serviço
//     $test=$EventWorkService->updateIsAceptOferta($idevento, $contactIdentity, $choiceOption);
   
   
//     // Retorna a resposta JSON após a operação
//     return response()->json(['message' => 'Eventos e trabalhadores atualizados com sucesso']);
// }


public function updateIsAceptOferta(Request $request) {
    // Cria uma instância do serviço
    $EventWorkService = new EventWorkService();
    
    // Obtém os parâmetros do request
    $idevento = $request->input('idevento');
    $contactIdentity = $request->input('contact_identity');
    $choiceOption = $request->input('choiceOption');
    
    // Converte idevento para int
    $idevento = intval($idevento);
    
    // Verifica se choiceOption é uma string e converte corretamente para booleano
    if ($choiceOption === "true") {
        $choiceOption = true;
    } elseif ($choiceOption === "false") {
        $choiceOption = false;
    }
    
    // Chama o método de atualização no serviço
    UpdateIsAceptOfertaJob::dispatch($idevento, $contactIdentity, $choiceOption);
   
   
    // Retorna a resposta JSON após a operação
    return response()->json(['message' => 'Eventos e trabalhadores atualizados com sucesso']);
}


public function getAllWorkersAndEventsWenAcceptisTrue(Request $request)

{

    $EventWorkService= new EventWorkService();
    return $EventWorkService->getWorkwhatIsAccepted();

}
public function getAllUsersRevcivedOfertaAndAccepted(Request $request){

    $EventWorkService= new EventWorkService();
    return $EventWorkService->getEventsInThreeDays();
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
