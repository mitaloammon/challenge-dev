<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Agendamentos extends Model
{
    use HasFactory;

    protected $fillable = [
        'data_agendamento',
        'hora_agendamento',
        'status',
        'token',
        'deleted',
        'created_at',
        'updated_at'
    ];

    protected $guarded = ['id'];

    protected $table = 'agendamentos';

    public function carros(): HasMany
    {
        return $this->hasMany(ConfigCarros::class);
    }
    public function motos(): HasMany
    {
        return $this->hasMany(ConfigMoto::class);
    }

   
}
