<?php

namespace App\Jobs;

use App\Services\CampainRequest;
use App\Services\GetModifyContact;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Log;

class EnviaMessagem implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $workersBatch;
    protected $event;
    protected $messageTemplateName;
    protected $contractid;
    protected $autoAuthorization;
    protected $flow_identifier;
    protected $stateId;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(array $workersBatch, array $event, $messageTemplateName, $contractid, $autoAuthorization, $flow_identifier, $stateId)
    {
        $this->workersBatch = $workersBatch;
        $this->event = $event;
        $this->messageTemplateName = $messageTemplateName;
        $this->contractid = $contractid;
        $this->autoAuthorization = $autoAuthorization;
        $this->flow_identifier = $flow_identifier;
        $this->stateId = $stateId;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $usersNumbers = []; // Acumula os números de telefone dos trabalhadores

        foreach ($this->workersBatch as $worker) {
            // Identidade do contato
            $contact_identity = ltrim($worker['telefone'], '+') . '@wa.gw.msging.net';

            // Instanciar o serviço de modificação de contato
            $updateContactService = new GetModifyContact(
                $contact_identity,
                $this->event['idevento'], // Passando o evento para o serviço
                $this->autoAuthorization, // A autorização automática
                $this->contractid // O ID do contrato
            );

            // Montar e atualizar o recurso do contato
            $resource = $updateContactService->montaResource($this->event['idevento'], $worker['id']);
            $updateContactService->updateContact($resource);

            // Adiciona o número do trabalhador ao array de usuários
            $userNumber = ltrim($worker['telefone'], '+');
            $usersNumbers[] = ['number' => $userNumber, 'valor' => $worker['valor']];
        }

        // Enviar a campanha para os trabalhadores após o loop
        $sendRequestBidActiveCampaign = new CampainRequest(
            $this->messageTemplateName,
            $usersNumbers,
            $this->autoAuthorization,
            $this->contractid,
            $this->flow_identifier,
            $this->stateId
        );

        // Chamada para o envio da campanha
        $response = $sendRequestBidActiveCampaign->sendRequest(
            $this->event['evento'],
            $this->event['data'],
            $this->event['hora'],
            $this->event['endereco'],
            $this->event['contato'],
            $usersNumbers
        );

        // Log para verificar se a campanha foi enviada
        Log::info("Campanha enviada com sucesso para os trabalhadores.", [
            'response' => $response
        ]);
    }
}
