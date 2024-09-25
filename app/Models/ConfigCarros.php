<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ConfigCarros extends Model
{
    use HasFactory;

    protected $fillable = [
        'marca',
        'modelo',
        'nome',
        'cor',
        'status',
        'token',
        'anexo',
        'observacao',
        'deleted',
        'ano_fabricacao',
        'quilometragem',
        'garantia',
        'created_at',
        'updated_at'
    ];

    protected $guarded = ['id'];

    protected $table = 'config_carros';


}
