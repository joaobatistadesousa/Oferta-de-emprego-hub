<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class UpdateIsAceptOfertaJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $idevento;
    public $contactIdentity;
    public $choiceOption;

    public function __construct($idevento, $contactIdentity, $choiceOption)
    {
        $this->idevento = $idevento;
        $this->contactIdentity = $contactIdentity;
        $this->choiceOption = $choiceOption;
    }

    public function handle()
    {
        $eventWorkService = new \App\Services\EventWorkService();
        $eventWorkService->updateIsAceptOferta($this->idevento, $this->contactIdentity, $this->choiceOption);
    }
}
