<?php
namespace App\Services;
class GetModifyContact
{
    private $contact_identity;
    private $eventId;
    private $authorization;
    private $contractid;

    public function __construct($contact_identity, $eventId, $authorization, $contractid)
    {
        $this->contact_identity = $contact_identity;
        $this->eventId = $eventId;
        $this->authorization = $authorization;
        $this->contractid = $contractid;
    }

    public function getContactData($identity)
    {
        $url = 'https://hubprolog.http.msging.net/commands';
        $headers = [
            'Authorization: ' . $this->authorization,
            'Content-Type: application/json'
        ];

        $body = json_encode([
            "id" => uniqid(),
            "to" => "postmaster@crm.msging.net",
            "method" => "get",
            "uri" => "/contacts/{$identity}"
        ]);


        // Inicializa o cURL
        $ch = curl_init($url);

        // Configura as opções do cURL
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $body);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

        // Executa a requisição
        $response = curl_exec($ch);

        // Verifica se houve erros
        if (curl_errno($ch)) {
            return null;
        }

        // Fecha a sessão cURL
        curl_close($ch);

        // Exibe a resposta

        return json_decode($response, true);
    }

    public function montaResource($event_id,$id_work)
    {
        // Obtém os dados do contato
        $data = $this->getContactData($this->contact_identity);

        // Verifica se houve um erro na requisição
        if ($data === null) {
            return null;
        }

        // Inicializa a variável para o novo recurso
        $new_resource = null;

        // Verifica se a resposta foi bem-sucedida
        if (isset($data['status']) && $data['status'] === "success") {
            // Cria o novo recurso incorporando o event_id
            $resource = $data['resource'];

            // Atualiza o campo extras, substituindo o event_id se já existir
            if (!isset($resource['extras'])) {
                $resource['extras'] = [];
            }
            $resource['extras']['event_id'] = $event_id;
            $resource['extras']['id_worker'] = $id_work; // Adiciona o id_work

            

            // Cria o novo recurso com o campo extras atualizado
            $new_resource = [
                'type' => $data['type'] ?? 'application/vnd.lime.contact+json',
                'resource' => $resource
            ];

            // Exibe o novo recurso criado


        } elseif (isset($data['status']) && $data['status'] === "failure" && isset($data['reason']['code']) && $data['reason']['code'] === 67) {
            // O recurso não foi encontrado, então cria um novo corpo de requisição
            $new_resource = [
                'type' => 'application/vnd.lime.contact+json',
                'resource' => [
                    'identity' => $this->contact_identity,
                    'extras' => [
                        'event_id' => $event_id,
                        'id_worker' => $id_work,
                        'source' => '{{$user_channel_name}}' // Adiciona o campo "source"
                    ]
                ]
            ];

            // Exibe o novo recurso criado quando não encontrado
        }

        // Retorna o novo recurso ou null se não foi criado
        return $new_resource;
    }


    public function updateContact($resource)
    {
        if ($resource !== null) {
            // Faz o merge do recurso utilizando cURL
            $curl = curl_init();

            $postFields = json_encode([
                'id' => uniqid(),
                'to' => 'postmaster@crm.msging.net',
                'method' => 'merge',
                'uri' => '/contacts',
                'type' => 'application/vnd.lime.contact+json',
                'resource' => $resource['resource']
            ]);

            curl_setopt_array($curl, [
                CURLOPT_URL => 'https://hubprolog.http.msging.net/commands',
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => '',
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 0,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => 'POST',
                CURLOPT_POSTFIELDS => $postFields,
                CURLOPT_HTTPHEADER => [
                    'Content-Type: application/json',
                    'Authorization: ' . $this->authorization
                ],
            ]);

            $response = curl_exec($curl);

            // Fecha a sessão cURL
            curl_close($curl);

            // Exibe a resposta
        } else {
            return null;
        }
    }
}
