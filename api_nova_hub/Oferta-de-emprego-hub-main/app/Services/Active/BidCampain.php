<?php
namespace App\Services\Active;


use App\Services\EventServices;
use App\Services\WorkerServices;
use App\Services\GetModifyContact;
use App\Services\EventWorkService;
use App\Services\CampainRequest;
use Illuminate\Support\Facades\Log;
use  App\Jobs\EnviaMessagem;
class BidCampain

{
    private $messageTemplateName;
    private $contractid;
    private $autoAuthorization;
    private $idSubBot;
    private $stateId;
    private $flow_identifier;

    public function __construct($contractid, $autoAuthorization, $idSubBot, $stateId, $flow_identifier, $messageTemplateName)
    {

        $this->messageTemplateName = $messageTemplateName;
        $this->contractid = $contractid;
        $this->autoAuthorization = $autoAuthorization;
        $this->idSubBot = $idSubBot;
        $this->stateId = $stateId;
        $this->flow_identifier = $flow_identifier;
    }
    public function getWorkers($idevento)
    {
        try {
            $works = [];

            // Pegando o evento
            $eventService = new EventServices();

            $event = $eventService->getEventById($idevento);

            if ($event instanceof \App\Models\Event) {
                $workers = new WorkerServices();

                // Adiciona os trabalhadores para o evento
                $works = $workers->listaTrabalhadoresQueNaoRecebemMensagens($event->idevento);
            }

            return [
                'events' => $event,
                'works' => $works,
            ];

        } catch (\Throwable $th) {
            return [
                'message' => 'Erro ao processar os eventos e trabalhadores',
                'error' => $th->getMessage(),
            ];
        }
    }
    

//     public function sendMessageBid(array $validatedData)
// {
    
// Log::info("este é um log de teste2");

// Log::info("validatedData: " . json_encode($validatedData));



// //     try {
// //         $event_work = new EventWorkService();

// //         // Dados do evento vindos diretamente do request
// //         $event = [
// //             'idevento' => $validatedData['idevento'],
// //             'evento' => $validatedData['evento'],
// //             'data' => $validatedData['data'],
// //             'hora' => $validatedData['hora'],
// //             'endereco' => $validatedData['endereco'] ?? null,
// //             'contato' => $validatedData['contato'] ?? null,
// //         ];

// //         $workers = $validatedData['workers']; // Lista de trabalhadores
// //         $batches = array_chunk($workers, 100); // Divide os trabalhadores em lotes de 100

// //         foreach ($batches as $batch) {
// //             $usersNumbers = []; // Reiniciar o array de números para cada lote

// //             foreach ($batch as $worker) {
// //                 $contact_identity = $worker['contact_identity'];

// // // Atualizar o contato
// //                 $updateContactService = new GetModifyContact(
// //                     $contact_identity,
// //                     $eventId,
// //                     $this->autoAuthorization,
// //                     $this->contractid
// //                 );
// //                 $resource = $updateContactService->montaResource($event['idevento'], $worker['id']);
// //                 $updateContactService->updateContact($resource);
// //                 // Corrigir o número do telefone e adicionar ao array
// //                 $userNumber = ltrim($worker['telefone'], '+');
// //                 $usersNumbers[] = [
// //                     'number' => $userNumber,
// //                     'valor' => $worker['valor'], // Inclui o valor do trabalhador
// //                 ];

// //                 // Atualizar a mensagem de disparo do evento
// //                 $event_work->updateTriggerMessageOfertaDisparo($event['idevento'], $contact_identity);
// //             }

// //             // Simula 500 ms para a requisição de envio
// //             //Criar e enviar a campanha para o lote atual
// //             $sendRequestBidActiveCampaign = new CampainRequest(
// //                 $this->messageTemplateName,
// //                 $usersNumbers,
// //                 $this->autoAuthorization,
// //                 $this->contractid,
// //                 $this->flow_identifier,
// //                 $this->stateId
// //             );

// //             $response = $sendRequestBidActiveCampaign->sendRequest(
// //                 $event['evento'],
// //                 $event['data'],
// //                 $event['hora'],
// //                 $event['endereco'],
// //                 $event['contato'],
// //                 $usersNumbers // Array completo de números
// //             );

// //             // Log de sucesso para cada lote
// //             Log::info("Lote de mensagens enviado com sucesso.", ['lote' => $usersNumbers, 'event' => $event,"workers" => $batch]);
// //         }

// //         // Retornar resposta de sucesso
// //         return response()->json([
// //             'message' => 'Mensagens enviadas com sucesso em lotes.',
// //             'event' => $event,
// //         ]);
// //     } catch (\Throwable $th) {
// //         // Capturar exceções e retornar erro
// //         return response()->json([
// //             'message' => 'Erro ao processar as mensagens.',
// //             'error' => $th->getMessage(),
// //         ], 500);
// //     }
// // }



// Log::info("este é um log de teste");

// }








// public function sendMessageBid(array $validatedData)
// {
//     // Log inicial dos dados validados

//     if (empty($validatedData['workers']) || !isset($validatedData['idevento'], $validatedData['evento'], $validatedData['data'], $validatedData['hora'], $validatedData['endereco'], $validatedData['contato'])) {
//         // Log::error("Dados validados faltando informações obrigatórias.");
//         return;
//     }

//     // Dividir os trabalhadores em lotes de 1000
//     $workersChunks = array_chunk($validatedData['workers'], 100);
//     $event = [
//         'idevento' => $validatedData['idevento'],
//         'evento' => $validatedData['evento'],
//         'data' => $validatedData['data'],
//         'hora' => $validatedData['hora'],
//         'endereco' => $validatedData['endereco'],
//         'contato' => $validatedData['contato'],
//     ];

//     $usersNumbers = []; // Inicialize a variável fora do loop para acumular os números dos trabalhadores

//     foreach ($workersChunks as $workersBatch) {
//         foreach ($workersBatch as $worker) {
//             // Contact identity
//             $contact_identity = ltrim($worker['telefone'], '+') . '@wa.gw.msging.net';

//             // Instanciar o serviço de modificação de contato
//             $updateContactService = new GetModifyContact(
//                 $contact_identity,
//                 $event['idevento'], // Passando o evento para o serviço
//                 $this->autoAuthorization, // A autorização automática
//                 $this->contractid // O ID do contrato
//             );

//             // Montar e atualizar o recurso do contato
//             $resource = $updateContactService->montaResource($event['idevento'], $worker['id']);
//             $updateContactService->updateContact($resource);

//             // Adiciona o número do trabalhador ao array de usuários
//             $userNumber = ltrim($worker['telefone'], '+');
//             $usersNumbers[] = ['number' => $userNumber, 'valor' => $worker['valor']];

//             // Log para verificar se o contato foi atualizado
//             // Log::info("Contato atualizado para o trabalhador", [
//             //     'worker' => $worker,
//             //     'contact_identity' => $contact_identity
//             // ]);
//         }
//     }

//     // Enviar a campanha após o loop, com todos os números de trabalhadores
//     $sendRequestBidActiveCampaign = new CampainRequest(
//         $this->messageTemplateName,
//         $usersNumbers,
//         $this->autoAuthorization,
//         $this->contractid,
//         $this->flow_identifier,
//         $this->stateId
//     );

//     $response = $sendRequestBidActiveCampaign->sendRequest(
//         $event['evento'],
//         $event['data'],
//         $event['hora'],
//         $event['endereco'],
//         $event['contato'],
//         $usersNumbers
//     );

//     Log::info("Campanha enviada com sucesso para os trabalhadores.", [
//         'response' => $response
//     ]);

//     $this->clearLaravelCaches();


// }

// protected function clearLaravelCaches()
// {
//     $commands = [
//         'php artisan config:clear',
//         'php artisan cache:clear',
//         'php artisan route:clear',
//         'php artisan view:clear',
//         'php artisan optimize:clear',
//         'composer dump-autoload',
//     ];

//     foreach ($commands as $command) {
//         exec($command, $output, $resultCode);
//         if ($resultCode !== 0) {
//         } else {
//         }
//     }
// }




//segunda tentativa 
public function sendMessageBid(array $validatedData)
{
    try {
        // Verificar se os dados obrigatórios estão presentes
        if (empty($validatedData['workers']) || !isset(
            $validatedData['idevento'],
            $validatedData['evento'],
            $validatedData['data'],
            $validatedData['hora'],
            $validatedData['endereco'],
            $validatedData['contato']
        )) {
            throw new \Exception("Dados estão faltando");
        }

        // Dividir os trabalhadores em lotes de 100
        $workersChunks = array_chunk($validatedData['workers'], 100);
        $event = [
            'idevento' => $validatedData['idevento'],
            'evento' => $validatedData['evento'],
            'data' => $validatedData['data'],
            'hora' => $validatedData['hora'],
            'endereco' => $validatedData['endereco'],
            'contato' => $validatedData['contato'],
        ];

        $messageTemplateName = $this->messageTemplateName;
        $contractid = $this->contractid;
        $autoAuthorization = $this->autoAuthorization;
        $idSubBot = $this->idSubBot;
        $stateId = $this->stateId;
        $flow_identifier = $this->flow_identifier;

        // Processar cada lote de trabalhadores
        foreach ($workersChunks as $workersBatch) {
            // Despachar um sub-job para processar o lote
            EnviaMessagem::dispatch(
                $workersBatch, 
                $event, 
                $messageTemplateName, 
                $contractid, 
                $autoAuthorization, 
                $idSubBot, 
                $stateId, 
                $flow_identifier
            );
        }

        Log::info("Sub-jobs para processamento de trabalhadores criados com sucesso.");
    } catch (\Throwable $th) {
        // Log do erro, capturando exceções e erros não tratados
        Log::error("Erro ao processar os dados.", ['error' => $th->getMessage()]);
    }
}
}
