<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ConfigMotos extends Model
{
    use HasFactory;

    protected $table = 'config_motos';

    protected $fillable = [
        'modelo',
        'marca',
        'cor',
        'status',
        'token',
        'anexo',
        'observacoes',
        'deleted',
        'created_at',
        'updated_at',
    ];

    protected $guarded = ['id'];
}
