<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
// use App\Services\Active\BidCampain;
use App\Services\Active\BidCampain; // Importe corretamente


class SendMessageBidJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $validatedData;

    /**
     * Cria uma nova instância do job.
     *
     * @param array $validatedData
     */
    public function __construct(array $validatedData)
    {
        $this->validatedData = $validatedData;
    }

    /**
     * Executa o job.
     *
     * @return void
     */
    public function handle()
    {
        $activeCapain= new BidCampain(
            "hubprolog",
            "Key cm90ZWFkb3JodWI6SWgzWUpIUkpkSWFpNklGMVR0cHE=",
            "ofertadeemprego",
            "3ecc242e-6064-4b6f-8a5a-ae74c587ef19",
            "74400ce9-0225-4359-b234-48948bb08c75",
            'oferta_emprego3'
        );
        // Obtém o idevento do corpo da requisição
    
    
        // Chame o método getWorkers e retorne os resultados
         $result = $activeCapain->sendMessageBid($this->validatedData);
            // Caso queira fazer algo com $result, adicione aqui.
    }
}
