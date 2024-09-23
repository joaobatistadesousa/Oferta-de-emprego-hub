<?php

namespace App\Services;

use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Request;

class BidRequestService
{
    private $messageTemplateName;
    private $tosendNumber;
    private $autorizationToken;
    private $contractid;

    public function __construct($messageTemplateName, $tosendNumber, $autorizationToken, $contractid)
    {
        $this->messageTemplateName = $messageTemplateName;
        $this->tosendNumber = $tosendNumber;
        $this->autorizationToken = $autorizationToken;
        $this->contractid = $contractid;
    }

    public function sendRequest($evento, $dia, $hora, $endereco, $contato, $valor)
    {
        // \dd($nameWork, $evento, $dia, $hora, $endereco, $contato, $valor, $this->messageTemplateName, $this->tosendNumber, $this->autorizationToken, $this->contractid);
        $client = new Client();
        $headers = [
            'Content-Type' => 'application/json',
            'Authorization' => $this->autorizationToken,
        ];

        // Substituindo variáveis no corpo da requisição
        $body = json_encode([
            "id" => "ce75f1c9-f33b-4b6a-8303-335c10b93360",
            "to" => $this->tosendNumber,
            "type" => "application/json",
            "content" => [
                "type" => "template",
                "template" => [
                    "name" => $this->messageTemplateName,
                    "language" => [
                        "code" => "pt_BR",
                        "policy" => "deterministic"
                    ],
                    "components" => [
                        [
                            "type" => "body",
                            "parameters" => [
                                ["type" => "text", "text" => $evento],
                                ["type" => "text", "text" => $dia],
                                ["type" => "text", "text" => $hora],
                                ["type" => "text", "text" => $endereco],
                                ["type" => "text", "text" => $contato],
                                ["type" => "text", "text" => $valor],
                            ]
                        ],
                        [
                            "type" => "button",
                            "sub_type" => "quick_reply",
                            "index" => 0,
                            "parameters" => [
                                ["type" => "payload", "payload" => "Sim"]
                            ]
                        ],
                        [
                            "type" => "button",
                            "sub_type" => "quick_reply",
                            "index" => 1,
                            "parameters" => [
                                ["type" => "payload", "payload" => "Não"]
                            ]
                        ]
                    ]
                ]
            ]
        ]);

        $url = "https://{$this->contractid}.http.msging.net/messages";
        $request = new Request('POST', $url, $headers, $body);
        $response = $client->sendAsync($request)->wait();

        // Retornar o código de status e o corpo da resposta
       
    }
}
