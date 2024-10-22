<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class Move_and_change_state_of_bot_or_sub_bot_service
{
    public $autoAuthorization;
    public $idSubBot;
    public $contractid;
    public $idBot;
    public $flow_identifier;
    public $stateId;

    public function __construct($autoAuthorization, $idSubBot, $contractid, $idBot, $flow_identifier, $stateId)
    {
        $this->autoAuthorization = $autoAuthorization;
        $this->idSubBot = $idSubBot;
        $this->contractid = $contractid;
        $this->idBot = $idBot;
        $this->flow_identifier = $flow_identifier;
        $this->stateId = $stateId;
        
    }

    public function changeOfBot($contact_identity)
    {
        
        //teste variaveis
       Log::info("dados no changeOfBot: ",
            ['contractid' => $this->contractid,'contact_identity' => $contact_identity,
                'idSubBot' => $this->idSubBot,
                'idBot' => $this->idBot,
                'stateId' => $this->stateId,
                'flow_identifier' => $this->flow_identifier,
                'autoAuthorization' => $this->autoAuthorization,
            ]);
            
        $response = Http::withHeaders([
            'Authorization' => $this->autoAuthorization,
            'Content-Type' => 'application/json',
        ])->post("https://{$this->contractid}.http.msging.net/commands", [
            "id" => "cfc715f2-f3fa-4b21-bf76-4c64c9a0f6df",
            "to" => "postmaster@msging.net",
            "method" => "set",
            "uri" => "/contexts/{$contact_identity}/Master-State",
            "type" => "text/plain",
            "resource" => "{$this->idSubBot}@msging.net"
        ]);

        return $response->body();
    }

    public function changeBlock($contact_identity)
    {
        $curl = curl_init();

        curl_setopt_array($curl, [
            CURLOPT_URL => 'https://http.msging.net/commands',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => json_encode([
                "id" => "301a83bc-e352-45c9-b704-b12368b00c6d",
                "to" => "postmaster@msging.net",
                "method" => "set",
                "uri" => "/contexts/{$contact_identity}/stateid@{$this->flow_identifier}",
                "type" => "text/plain",
                "resource" => $this->stateId
            ]),
            CURLOPT_HTTPHEADER => [
                "Authorization: {$this->autoAuthorization}",
                'Content-Type: application/json',
            ],
        ]);

        $response = curl_exec($curl);
        curl_close($curl);

        return $response;
    }
}
