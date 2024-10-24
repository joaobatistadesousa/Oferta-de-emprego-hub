<?php
namespace  App\Services\active;

use App\Services\EventServices;
use App\Services\WorkerServices;
use App\Services\GetModifyContact;
use App\Services\EventWorkService;
use App\Services\CampainRequest;
use Illuminate\Support\Facades\Log;
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
    // public function sendMessageBid($idevento)
    // {
    //     $usersNumbers = [];
    //     try {
    //         $event_work = new EventWorkService();
    
    //         // Obtém eventos e trabalhadores para o evento específico
    //         $result = $this->getWorkers($idevento);
    
    //         // Verifica se o retorno contém 'events' e 'works'
    //         if (isset($result['events']) && isset($result['works'])) {
    //             // Como 'events' é um objeto, não um array, ajustamos a lógica
    //             $event = $result['events'];  // Acessa diretamente o evento
    //             $eventId = $event->idevento;
    //             $workers = $result['works']; // Obtém a lista de trabalhadores
    
    //             $allMessagesSent = true;
    
    //             foreach ($workers as $worker) {
    //                 $contact_identity = $worker->contact_identity;
    
    //                 // Atualiza o contato usando o serviço de atualização
    //                 $updateContactService = new GetModifyContact(
    //                     $contact_identity,
    //                     $eventId,
    //                     $this->autoAuthorization,
    //                     $this->contractid
    //                 );
    //                 $resource = $updateContactService->montaResource($eventId, $worker->id_work);
    //                 $updateContactService->updateContact($resource);
    
    //                 // Corrige o número do telefone removendo o símbolo "+"
    //                 $userNumber = ltrim($worker->telefone, '+');
    //                 //add no array usersNumbers

    //                 array_push($usersNumbers, $userNumber);
    
                    
    //                 // // Cria instância do serviço para enviar a requisição da campanha
    //                 // return response()->json([
    //                 //     $this->messageTemplateName,
    //                 //     $usersNumbers,
    //                 //     $this->autoAuthorization,
    //                 //     $this->contractid,
    //                 //     $this->flow_identifier,
    //                 //     $this->stateId 
    //                 // ]);

    //                 $sendRequestBidActiveCampaign = new CampainRequest(
    //                     $this->messageTemplateName,
    //                     $usersNumbers,
    //                     $this->autoAuthorization,
    //                     $this->contractid,
    //                     $this->flow_identifier,
    //                     $this->stateId
    //                 );
    
    //                 // Envia a requisição da campanha
    //                  $response = $sendRequestBidActiveCampaign->sendRequest(
    //                     $event->evento,
    //                     $event->data,
    //                     $event->hora,
    //                     $event->endereco,
    //                     $event->contato,
    //                     $event->valor
    //                 );
    
    //                 $event_work->updateTriggerMessageOfertaDisparo($event->idevento, $worker->contact_identity);
    //             }
    //             Log::info("estou no sendMessageBidCampain");
    //             // Retorna sucesso e dados
    //             if ($allMessagesSent) {
    //                 return response()->json([
    //                     'message' => 'Mensagens enviadas com sucesso.',
    //                     'events' => $result['events'],
    //                     'workers' => $result['works']
    //                 ]);
    //             } else {
    //                 return response()->json([
    //                     'message' => 'Algumas mensagens não foram enviadas.',
    //                     'events' => $result['events'],
    //                     'workers' => $result['works']
    //                 ], 207); // HTTP Status 207 - Multi-Status
    //             }
    //         } else {
    //             // Retorna mensagem de erro se não houver eventos ou trabalhadores
    //             return response()->json([
    //                 'message' => 'Eventos ou trabalhadores não encontrados.'
    //             ], 404);
    //         }
    //     } catch (\Throwable $th) {
    //         // Captura exceções e retorna erro
    //         return response()->json([
    //             'message' => 'Erro ao processar os eventos e trabalhadores.',
    //             'error' => $th->getMessage()
    //         ], 500);
    //     }
    // }

    public function sendMessageBid($idevento)
{
    $usersNumbers = []; // Initialize an empty array to collect user numbers
    try {
        $event_work = new EventWorkService();

        // Obtain events and workers for the specific event
        $result = $this->getWorkers($idevento);

        // Check if the result contains 'events' and 'works'
        if (isset($result['events']) && isset($result['works'])) {
            $event = $result['events']; // Access the event directly
            $eventId = $event->idevento;
            $workers = $result['works']; // Get the list of workers

            $allMessagesSent = true;

            foreach ($workers as $worker) {
                $contact_identity = $worker->contact_identity;

                // Update the contact using the update service
                $updateContactService = new GetModifyContact(
                    $contact_identity,
                    $eventId,
                    $this->autoAuthorization,
                    $this->contractid
                );
                $resource = $updateContactService->montaResource($eventId, $worker->id_work);
                $updateContactService->updateContact($resource);

                // Correct the phone number by removing the "+" symbol
                $userNumber = ltrim($worker->telefone, '+');
                // Add to the array of users' numbers
                array_push($usersNumbers, $userNumber);

                // Update the trigger message
                $event_work->updateTriggerMessageOfertaDisparo($event->idevento, $worker->contact_identity);
            }

            // Create an instance of the service to send the campaign request
            $sendRequestBidActiveCampaign = new CampainRequest(
                $this->messageTemplateName,
                array_unique($usersNumbers), // Ensure unique user numbers
                $this->autoAuthorization,
                $this->contractid,
                $this->flow_identifier,
                $this->stateId
            );

            // Send the campaign request outside the loop
            $response = $sendRequestBidActiveCampaign->sendRequest(
                $event->evento,
                $event->data,
                $event->hora,
                $event->endereco,
                $event->contato,
                $event->valor
            );

            Log::info("estou no sendMessageBidCampain");
            // Return success and data
            return response()->json([
                'message' => 'Mensagens enviadas com sucesso.',
                'events' => $result['events'],
                'workers' => $result['works']
            ]);
        } else {
            // Return error message if no events or workers found
            return response()->json([
                'message' => 'Eventos ou trabalhadores não encontrados.'
            ], 404);
        }
    } catch (\Throwable $th) {
        // Capture exceptions and return error
        return response()->json([
            'message' => 'Erro ao processar os eventos e trabalhadores.',
            'error' => $th->getMessage()
        ], 500);
    }
}

    

}
