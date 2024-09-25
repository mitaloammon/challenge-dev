<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Clientes extends Model
{
    use HasFactory;

    protected $fillable = [
        'nome',
        'telefone',
        'email',
        'endereco',
        'status',
        'deleted',
        'created_at',
        'updated_at'
    ];

    protected $guarded = ['id'];

    protected $table = 'clientes';
}
