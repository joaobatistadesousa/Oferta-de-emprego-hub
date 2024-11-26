<?php
namespace App\Services;

use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Request;
use Ramsey\Uuid\Uuid;
use Illuminate\Support\Facades\Log;

class CampainRequest
{
    private $messageTemplateName;
    private $usersNumbers;
    private $authorizationToken;
    private $contractId;
    private $flowIdentifier;
    private $stateId;

    public function __construct($messageTemplateName, $usersNumbers, $authorizationToken, $contractId, $flowIdentifier, $stateId)
    {
        $this->messageTemplateName = $messageTemplateName;
        $this->usersNumbers = $usersNumbers;
        $this->authorizationToken = $authorizationToken;
        $this->contractId = $contractId;
        $this->flowIdentifier = $flowIdentifier;
        $this->stateId = $stateId;
        
    }

    public function CreateObject_audience($usersNumbers, $evento, $dia, $hora, $endereco, $contato, $valor)
    {
        $audiences = []; // Initialize an empty array to hold audience objects

        foreach ($usersNumbers as $userData) {
            $audience = [
                "recipient" => $userData['number'],
                "messageParams" => [
                    "1" => $evento,
                    "2" => $dia,
                    "3" => $hora,
                    "4" => $endereco,
                    "5" => $contato,
                    "6" => $userData['valor'] // Pass individual worker value
                ]
            ];

            $audiences[] = $audience; // Add the audience object to the array
        }

        return $audiences; // Return the complete audiences array
    }

    public function sendRequest($evento, $dia, $hora, $endereco, $contato, $usersNumbers)
    {
        Log::info("estou no SendRequestBidActiveCampain");
        Log::info("todas as variaveis");
    
        $client = new Client();
        $headers = [
            'Authorization' => $this->authorizationToken,
            'Content-Type' => 'application/json'
        ];
    
        // Itera sobre `usersNumbers` para criar `audiences` com valores específicos
        $audiences = [];
        foreach ($usersNumbers as $user) {
            $audience = [
                "recipient" => $user['number'],
                "messageParams" => [
                    "1" => $evento,
                    "2" => $dia,
                    "3" => $hora,
                    "4" => $endereco,
                    "5" => $contato,
                    "6" => $user['valor'] // Usa o valor específico do trabalhador
                ]
            ];
            $audiences[] = $audience;
        }
    
        $body = json_encode([
            "id" => Uuid::uuid4()->toString(),
            "to" => "postmaster@activecampaign.msging.net",
            "method" => "set",
            "uri" => "/campaign/full",
            "type" => "application/vnd.iris.activecampaign.full-campaign+json",
            "resource" => [
                "campaign" => [
                    "name" => "Disparo da oferta emprego " ." nome do evento ". $evento ." - " . Uuid::uuid4()->toString(),
                    "campaignType" => "Batch",
                    "flowId" => $this->flowIdentifier,
                    "stateId" => $this->stateId,
                    "masterState" => "ofertadeemprego@msging.net"
                ],
                "audiences" => $audiences,
                "message" => [
                    "messageTemplate" => $this->messageTemplateName,
                    "messageParams" => [
                        "1",
                        "2",
                        "3",
                        "4",
                        "5",
                        "6"
                    ]
                ]
            ]
        ]);
    
        $request = new Request('POST', "https://{$this->contractId}.http.msging.net/commands", $headers, $body);
        try {
            $res = $client->sendAsync($request)->wait();
            $responseBody = json_decode($res->getBody());
            Log::info("Resposta da API: " . $res->getStatusCode() . ' - ' . $res->getBody());
            return $responseBody;

        } catch (\Exception $e) {
            Log::error("Erro ao enviar requisição: " . $e->getMessage());
            return $e->getMessage();
        }
    }
    
}
