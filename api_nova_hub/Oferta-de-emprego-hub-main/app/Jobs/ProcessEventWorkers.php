<?php
namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB; // Importação da facade DB
use App\Models\Event;
use App\Models\Worker;
use App\Models\WorkersEvent;
use Carbon\Carbon;
use App\Jobs\SendMessageBidJob; // Importação do Job de envio de mensagens


class ProcessEventWorkers implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $validatedData;

    public function __construct(array $validatedData)
    {
        $this->validatedData = $validatedData;
    }
    public function limparEndereco($endereco) {
        // Usa preg_replace para substituir qualquer variação de [BR] por espaços em branco
        $enderecoLimpo = preg_replace(["/\[br\]/i", "/\n/", "/\t/", "/\s{2,}/"], ["", "", "", " "], $endereco);
    $enderecoLimpo = trim($enderecoLimpo); // Remove espaços extras no início e no final
        
        // Retorna o endereço sem as variações de [BR]
        return $enderecoLimpo;
    }
    public function handle()
    {
        $this->validatedData['data'] = Carbon::createFromFormat('d/m/Y', $this->validatedData['data'])->format('Y-m-d');
        $this->validatedData['endereco'] = $this->limparEndereco($this->validatedData['endereco']);

        DB::transaction(function () {
            $telefonesInvalidos = [];

            // Criar ou atualizar o evento
            $event = Event::updateOrCreate(
                ['idevento' => $this->validatedData['idevento']],
                [
                    'evento' => $this->validatedData['evento'],
                    'data' => $this->validatedData['data'],
                    'hora' => $this->validatedData['hora'],
                    'contato' => $this->validatedData['contato'],
                    'endereco' => $this->validatedData['endereco']
                ]
            );

            // Dividir os trabalhadores em lotes de 1000
            $workersChunks = array_chunk($this->validatedData['workers'], 4000);

            foreach ($workersChunks as $workersBatch) {
                foreach ($workersBatch as $workerData) {
                    // Validar telefone
                    if (!preg_match('/^\+55\d{11}$/', $workerData['telefone'])) {
                        $telefonesInvalidos[] = $workerData;
                        continue;
                    }

                    // Inserir ou atualizar o trabalhador
                    $worker = Worker::updateOrCreate(
                        ['id_work' => $workerData['id']],
                        [
                            'contact_identity' => str_replace('+', '', $workerData['telefone']) . "@wa.gw.msging.net",
                            'nome' => $workerData['nome'],
                            'telefone' => $workerData['telefone'],
                        ]
                    );

                    // Inserir ou atualizar relação na tabela pivot
                    WorkersEvent::updateOrCreate(
                        [
                            'worker_id' => $worker->id,
                            'idevento' => $event->idevento
                        ],
                        [
                            'valor' => $workerData['valor'],
                            'aceitou' => false,
                            'triggerMessageOferta' => 1,
                            'triggerMessageLembrete' => false,
                        ]
                    );
                }
            }

            // Lidar com números inválidos
            if (!empty($telefonesInvalidos)) {
                logger()->warning('Telefones inválidos encontrados.', ['telefones' => $telefonesInvalidos]);
            }
            $this->dispatchSendMessageJob($this->validatedData);

        });

    }
    protected function dispatchSendMessageJob(array $validatedData)
    {
        

        // Despacha o job SendMessageBidJob
        SendMessageBidJob::dispatch($validatedData);
    }

}
