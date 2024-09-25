<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Historico extends Model
{
    use HasFactory;

    protected $fillable = [
        'data',
        'observacoes',
        'status',
        'deleted',
        'created_at',
        'updated_at',
        'token'
    ];

    protected $guarded = ['id'];

    protected $table = 'historicos';

    public function carros(): HasMany
    {
        return $this->hasMany(ConfigCarros::class);
    }
    public function motos(): HasMany
    {
        return $this->hasMany(ConfigMoto::class);
    }
}
