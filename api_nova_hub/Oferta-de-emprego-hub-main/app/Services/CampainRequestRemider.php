<?php
namespace App\Services;

use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Request;
use Ramsey\Uuid\Uuid;
use Illuminate\Support\Facades\Log;

class CampainRequestRemider
{
    private $messageTemplateName;
    private $usersNumbers;
    private $authorizationToken;
    private $contractId;
    

    public function __construct($messageTemplateName, $usersNumbers, $authorizationToken, $contractId)
    {
        $this->messageTemplateName = $messageTemplateName;
        $this->usersNumbers = $usersNumbers;
        $this->authorizationToken = $authorizationToken;
        $this->contractId = $contractId;
        
        Log::info("array de numeros campainRequest: " . json_encode($this->usersNumbers));
    }
    public function CreateObject_audience($usersNumbers,$data,$endereco)
{
    $audiences = []; // Initialize an empty array to hold audience objects

    foreach ($usersNumbers as $number) {
        $audience = [
            "recipient" => $number,
            "messageParams" => [
                "1" => $data,
                "2" => $endereco
                
            ]
        ];

        $audiences[] = $audience; // Add the audience object to the array
    }

    return $audiences; // Return the complete audiences array
}


public function sendRequest($data,$endereco)
{
    Log::info("estou no SendRequestBidActiveCampain");
Log::info("todas as variaveis");
// Initialize the HTTP client
    $client = new Client();
    $headers = [
        'Authorization' => $this->authorizationToken,
        'Content-Type' => 'application/json'
    ];

    // Create audiences array
    $audiences = $this->CreateObject_audience($this->usersNumbers, $data, $endereco);

    // Create request body dynamically
    $body = json_encode([
        "id" => Uuid::uuid4()->toString(), // Generate a unique ID for the request
        "to" => "postmaster@activecampaign.msging.net",
        "method" => "set",
        "uri" => "/campaign/full",
        "type" => "application/vnd.iris.activecampaign.full-campaign+json",
        "resource" => [
            "campaign" => [
                "name" => "Disparo de lembrete via campaign" . Uuid::uuid4()->toString(),
                "campaignType" => "Batch",
                "flowId" => null,
                "stateId" => null,
                "masterState" => null
            ],
            "audiences" => $audiences, // Use the dynamically created audiences
            "message" => [
                "messageTemplate" => $this->messageTemplateName,
                "messageParams" => [
                    "1",
                    "2",
                ]
            ]
        ]
    ]);

    // Create the request
    $request = new Request('POST', "https://{$this->contractId}.http.msging.net/commands", $headers, $body);
    $res = $client->sendAsync($request)->wait();

    // Output the response
    return $res->getBody();}

}
