<?php

namespace App\Services;

use App\Services\EventWorkService;
use App\Services\Move_and_change_state_of_bot_or_sub_bot_service;
use App\Services\ReminderRequestService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
class SendMessageReminder
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

    public function getWorkers()
    {
        try {
            $eventWorkService = new EventWorkService();
            $response = $eventWorkService->getEventsInThreeDays();

            // Verifica se a resposta é uma instância de JsonResponse
            if ($response instanceof \Illuminate\Http\JsonResponse) {
                $events = $response->getData(true); // Converte a resposta JSON para um array associativo
            } else {
                throw new \Exception('Resposta inesperada do serviço de eventos.');
            }

            // Processa os eventos e trabalhadores
            foreach ($events['events'] as $event) {
                if (isset($event['workers']) && is_array($event['workers']) && !empty($event['workers'])) {
                    // Workers array is not empty
                    return $events;
                }
            }

            return false;

        } catch (\Throwable $th) {
            return [
                'message' => 'Erro ao processar os eventos e trabalhadores',
                'error' => $th->getMessage(),
            ];
        }
    }
    public function sendMessageReminder() {
        try {
            $eventWorkService = new EventWorkService();
            $events = $this->getWorkers();
            Log::info("events: " . json_encode($events));
            
            if (!$events || !isset($events['events'])) {
                return false;
            }
    
            foreach ($events['events'] as $event) {
                $eventId = $event['idevento'];
                $workers = $event['workers'];
    
                // Formata a data do evento para o formato desejado
                $formattedDate = Carbon::parse($event['data'])->format('d/m/Y');
    
                foreach ($workers as $worker) {
                    $contact_identity = $worker['contact_identity'];
    
                    // Cria um serviço para enviar a mensagem de lembrete
                    $reminderRequestService = new ReminderRequestService(
                        $this->messageTemplateName,
                        $contact_identity,
                        $this->autoAuthorization,
                        $this->contractid
                    );
    
                    // Envia a requisição de lembrete
                    $reminderRequestService->sendRequest(
                        $formattedDate,
                        $event['endereco']
                    );
    
                    // Atualiza o trigger de mensagem lembrete para o trabalhador
                    $eventWorkService->updateTriggerMessageLembrete($eventId, $contact_identity);
                }
            }
    
        } catch (\Throwable $th) {
            return [
                'message' => 'Erro ao processar os eventos e trabalhadores',
                'error' => $th->getMessage(),
            ];
        }
    }
    
}