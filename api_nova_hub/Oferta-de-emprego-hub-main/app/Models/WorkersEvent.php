<?php

// app/Models/WorkersEvent.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WorkersEvent extends Model
{
    use HasFactory;

    protected $table = 'workers_event';

    public $timestamps = false;
    protected $fillable = [
        'worker_id',
        'idevento',
        'aceitou',
        'triggerMessageOferta',
        'triggerMessageLembrete',
    ];

    public function worker()
    {
        return $this->belongsTo(Worker::class, 'worker_id');
    }

    public function event()
    {
        return $this->belongsTo(Event::class, 'idevento');
    }
}
