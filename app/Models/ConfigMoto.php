<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ConfigMoto extends Model
{
    use HasFactory;

    protected $fillable = [
        'modelo',
        'nome',
        'nome_dono',
        'marca',
        'cor',
        'status',
        'token',
        'anexo',
        'observacoes',
        'deleted',
        'ano_fabricacao',
        'quilometragem',
        'garantia',
        'created_at',
        'updated_at'
    ];

    protected $guarded = ['id'];

    protected $table = 'config_motos';
}
