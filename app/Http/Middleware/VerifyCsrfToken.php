<?php

namespace App\Http\Middleware;

use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken as Middleware;

class VerifyCsrfToken extends Middleware
{
    /**
     * The URIs that should be excluded from CSRF verification.
     *
     * @var array<int, string>
     */
    protected $except = [
        //
        /* Listagem para via de testes no back end da aplicação utilizando o Postman API */
        
        'http://localhost:8000/ConfigMotos',
        'http://localhost:8000/ConfigCarros',
        'http://localhost:8000/ClientesController',
        'http://localhost:8000/HistoricosController',
        'http://localhost:8000/AgendamentosController'
    ];
}
