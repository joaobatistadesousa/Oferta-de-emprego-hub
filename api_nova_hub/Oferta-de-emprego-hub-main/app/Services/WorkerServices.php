<?php
namespace App\Services;
use Illuminate\Support\Facades\DB;
class WorkerServices{
    function listaTrabalhadoresQueNaoRecebemMensagens($eventId)
    {
        $workers = DB::table('workers as w')
            ->join('workers_event as we', 'w.id', '=', 'we.worker_id')
            ->where('we.idevento', $eventId) // Usando o nome correto da coluna
            ->where(function ($query) {
                $query->whereNull('we.triggerMessageOferta')
                      ->orWhere('we.triggerMessageOferta', 0);
            })
            ->select('w.id', 'w.id_work', 'w.contact_identity', 'w.nome', 'w.telefone', 'we.valor') // Incluindo o campo valor
            ->get();
    
        return $workers;
    }
    

}
