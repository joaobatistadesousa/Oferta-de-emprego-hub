<?php

// app/Models/Event.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Event extends Model
{
    use HasFactory;

    protected $primaryKey = 'idevento';
    public $incrementing = false;
    public $timestamps = false;
    public $table = 'events';
    protected $keyType = 'integer';

    protected $fillable = [
        'idevento',
        'evento',
        'data',
        'hora',
        'contato',
        'valor',
        'endereco',
    ];

    public function workers()
    {
        return $this->belongsToMany(Worker::class, 'workers_event', 'idevento', 'worker_id');
    }
}
