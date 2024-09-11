<?php

namespace App\Models;

// app/Models/Worker.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Worker extends Model
{
    use HasFactory;

    public $table = 'workers';
    public $timestamps = false;
    protected $fillable = [
        'id_work',
        'contact_identity',
        'nome',
        'telefone',
    ];

    public function events()
    {
        return $this->belongsToMany(Event::class, 'workers_event', 'worker_id', 'idevento');
    }
}
