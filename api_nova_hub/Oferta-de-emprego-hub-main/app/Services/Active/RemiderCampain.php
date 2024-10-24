<?php

namespace App\Services\Active;

use App\Services\EventWorkService;
use App\Services\CampainRequestRemider;

use Carbon\Carbon;
class RemiderCampain
{
    private $messageTemplateName;
    private $contractid;
    private $autoAuthorization;
    private $idSubBot;
    private $stateId;
    private $flow_identifier;

    public function __construct($contractid, $autoAuthorization,  $messageTemplateName)
    {
        $this->messageTemplateName = $messageTemplateName;
        $this->contractid = $contractid;
        $this->autoAuthorization = $autoAuthorization;

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
        $usersNumbers = []; // Initialize an array to collect user numbers
        try {
            $eventWorkService = new EventWorkService();
            $events = $this->getWorkers();
    
            if (!$events) {
                return false;
            }
    
            foreach ($events['events'] as $event) {
                $eventId = $event['idevento'];
                $workers = $event['workers'];
    
                foreach ($workers as $worker) {
                    $contact_identity = $worker['contact_identity'];
    
                    // Format the event date using Carbon
                    $formataData = $event['data'];
                    $event['data'] = Carbon::parse($formataData)->format('d/m/Y');
    
                    // Correct the phone number by removing the "+" symbol
                    $userNumber = ltrim($worker['telefone'], '+');
    
                    // Add the user number to the array, ensuring no duplicates
                    array_push($usersNumbers, $userNumber);
    
                    // Update trigger message for the worker
                    $eventWorkService->updateTriggerMessageLembrete($eventId, $contact_identity);
                }
    
                // Ensure unique user numbers before sending the request
                $uniqueUserNumbers = array_unique($usersNumbers);
    
                // Create the reminder request service and send the request
                $reminderRequestService = new CampainRequestRemider(
                    $this->messageTemplateName
                );
                $reminderRequestService->sendRequest(
                    $event['data'],
                    $event['endereco'],
                    $uniqueUserNumbers // Send the unique user numbers
                );
            }
    
        } catch (\Throwable $th) {
            return [
                'message' => 'Erro ao processar os eventos e trabalhadores',
                'error' => $th->getMessage(),
            ];
        }
    }
    
}
