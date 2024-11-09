<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Event;
use App\Models\Worker;
use App\Models\WorkersEvent;
// use App\Services\SendMessageBid;
use App\Services\Active\BidCampain;
use App\Services\EventWorkService;
use Illuminate\Support\Facades\Log;


class EventWorkController extends Controller
{
 // app/Http/Controllers/EventController.php

 function limparEndereco($endereco) {
    // Usa preg_replace para substituir qualquer variação de [BR] por espaços em branco
    $enderecoLimpo = preg_replace(["/\[br\]/i", "/\n/", "/\t/", "/\s{2,}/"], ["", "", "", " "], $endereco);
$enderecoLimpo = trim($enderecoLimpo); // Remove espaços extras no início e no final
    
    // Retorna o endereço sem as variações de [BR]
    return $enderecoLimpo;
}

public function storeOrUpdate(Request $request)
{
    // Validar dados
    $validatedData = $request->validate([
        'idevento' => 'required|integer',
        'evento' => 'required|string|max:255',
        'data' => 'required|date_format:d/m/Y',
        'hora' => 'required|date_format:H:i:s',
        'contato' => 'nullable|string|max:255',
        'endereco' => 'nullable|string',
        'workers' => 'required|array',
        'workers.*.id' => 'required|string',
        'workers.*.nome' => 'required|string|max:255',
        'workers.*.telefone' => 'required|string|max:20',
        'workers.*.valor' => 'required|numeric', // Verificação de valor numérico
    ]);

    // Converter a data para o formato do banco de dados
    $validatedData['data'] = \Carbon\Carbon::createFromFormat('d/m/Y', $validatedData['data'])->format('Y-m-d');
    $validatedData['endereco'] = $this->limparEndereco($validatedData['endereco']);

    // Inserir ou atualizar o evento
    $event = Event::updateOrCreate(
        ['idevento' => $validatedData['idevento']],
        [
            'evento' => $validatedData['evento'],
            'data' => $validatedData['data'],
            'hora' => $validatedData['hora'],
            'contato' => $validatedData['contato'],
            'endereco' => $validatedData['endereco']
        ]
    );
    

    // Processar cada trabalhador
    foreach ($validatedData['workers'] as $workerData) {

        // Verificar se o valor está presente e é numérico
        if (!isset($workerData['valor']) || !is_numeric($workerData['valor'])) {
            Log::error('Valor inválido ou ausente para o trabalhador: ' . json_encode($workerData));
            continue;  // Pular para o próximo trabalhador se o valor não estiver presente ou for inválido
        }
        
        // Remover o sinal de mais (+) do telefone
        $telefoneSemMais = str_replace('+', '', $workerData['telefone']);
        
        // Verificar se o telefone começa com '5555' e substituir por '55'
        if (substr($telefoneSemMais, 0, 4) === '5555') {
            $telefoneSemMais = '55' . substr($telefoneSemMais, 4);
        }
        
        // Adicionar '+55' se o telefone não começar com esse prefixo
        if (substr($telefoneSemMais, 0, 2) !== '55') {
            $telefoneSemMais = '55' . $telefoneSemMais;
        }
    
        // Adicionar o '+' de volta no início do telefone
        $telefoneFormatado = '+' . $telefoneSemMais;
    
        // Formatar o contact_identity
        $contactIdentity = $telefoneSemMais . "@wa.gw.msging.net";
    
        // Inserir ou atualizar o trabalhador
        $worker = Worker::updateOrCreate(
            ['id_work' => $workerData['id']],
            [
                'contact_identity' => $contactIdentity,
                'nome' => $workerData['nome'],
                'telefone' => $telefoneFormatado,
            ]
        );
    
        // Inserir ou atualizar a relação na tabela pivot com o valor de cada trabalhador
        
        WorkersEvent::updateOrCreate(
            [
                'worker_id' => $worker->id,
                'idevento' => $event->idevento
            ],
            [
                'valor' => $workerData['valor'],  // O valor que está sendo passado
                'aceitou' => false,
                'triggerMessageOferta' => false,
                'triggerMessageLembrete' => false,
            ]
        );
    } 
    
    
    //   $activeCapain= new BidCampain(
    //     "hubprolog",
    //     "Key cm90ZWFkb3JodWI6SWgzWUpIUkpkSWFpNklGMVR0cHE=",
    //     "ofertadeemprego",
    //     "3ecc242e-6064-4b6f-8a5a-ae74c587ef19",
    //     "74400ce9-0225-4359-b234-48948bb08c75",
    //     'oferta_emprego3'
    // );
    // // Obtém o idevento do corpo da requisição


    // // Chame o método getWorkers e retorne os resultados
    //  $result = $activeCapain->sendMessageBid($request->input('idevento'));

    // Retorna a resposta   


    return response()->json([
        'message' => 'Eventos e trabalhadores criados/atualizados com sucesso',
    ]);

}






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
    $test=$EventWorkService->updateIsAceptOferta($idevento, $contactIdentity, $choiceOption);
   
   
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
