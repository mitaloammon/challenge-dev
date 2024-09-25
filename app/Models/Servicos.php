<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Servicos extends Model
{
    use HasFactory;

    protected $fillable = [
        'descricao',
        'preco',
        'tempo_estimado',
        'deleted',
        'created_at',
        'updated_at',
        'token'
    ];

    protected $guarded = ['id'];

    protected $table = 'servicos';
}
