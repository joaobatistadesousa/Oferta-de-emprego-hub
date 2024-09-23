<?php
namespace App\Services;
use App\Services\BidRequestService;
use App\Services\EventServices;
use App\Services\WorkerServices;
use App\Services\Move_and_change_state_of_bot_or_sub_bot_service;
use App\Services\GetModifyContact;
use App\Services\EventWorkService;
class SendMessageBid
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
    public function sendMessageBid($idevento)
{
    try {
        $event_work = new EventWorkService();

        // Obtém eventos e trabalhadores para o evento específico
        $result = $this->getWorkers($idevento);

        // Verifica se o retorno contém 'events' e 'works'
        if (isset($result['events']) && isset($result['works'])) {
            // Como 'events' é um objeto, não um array, ajustamos a lógica
            $event = $result['events'];  // Acessa diretamente o evento
            $eventId = $event->idevento;
            $workers = $result['works']; // Obtém a lista de trabalhadores

            $allMessagesSent = true;

            foreach ($workers as $worker) {
                $contact_identity = $worker->contact_identity;

                // Realiza as mudanças necessárias no bot e bloqueio
                $moveAndChangeStateService = new Move_and_change_state_of_bot_or_sub_bot_service(
                    $this->autoAuthorization,
                    $this->idSubBot,
                    $this->contractid,
                    $this->idSubBot,
                    $this->flow_identifier,
                    $this->stateId
                );

                $moveAndChangeStateService->changeOfBot($contact_identity);
                $moveAndChangeStateService->changeBlock($contact_identity);

                // Atualiza o contato usando o serviço de atualização
                $updateContactService = new GetModifyContact(
                    $contact_identity,
                    $eventId,
                    $this->autoAuthorization,
                    $this->contractid
                );

                $resource = $updateContactService->montaResource($eventId);
                $updateContactService->updateContact($resource);

                $bidRequestService = new BidRequestService(
                    $this->messageTemplateName,
                    $worker->contact_identity,
                    $this->autoAuthorization,
                    $this->contractid
                );

                $response = $bidRequestService->sendRequest(
                    $event->evento,
                    $event->data,
                    $event->hora,
                    $event->endereco,
                    $event->contato,
                    $event->valor
                );


  $event_work->updateTriggerMessageOfertaDisparo($event->idevento, $worker->contact_identity);
            }

            // Retorna sucesso e dados
            if ($allMessagesSent) {
                return response()->json([
                    'message' => 'Mensagens enviadas com sucesso.',
                    'events' => $result['events'],
                    'workers' => $result['works']
                ]);
            } else {
                return response()->json([
                    'message' => 'Algumas mensagens não foram enviadas.',
                    'events' => $result['events'],
                    'workers' => $result['works']
                ], 207); // HTTP Status 207 - Multi-Status
            }
        } else {
            // Retorna mensagem de erro se não houver eventos ou trabalhadores
            return response()->json([
                'message' => 'Eventos ou trabalhadores não encontrados.'
            ], 404);
        }
    } catch (\Throwable $th) {
        // Captura exceções e retorna erro
        return response()->json([
            'message' => 'Erro ao processar os eventos e trabalhadores.',
            'error' => $th->getMessage()
        ], 500);
    }
}

}
