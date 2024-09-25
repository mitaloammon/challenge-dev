<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreAgendamentosPost extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules()
    {
        return [
            //
            'status'            => 'required|string',
            'data_agendamento'  => 'required',
            'hora_agendamento'  => 'required',
            'token'             => 'required',
            'deleted'           => 'required|string',
            'created_at'        => 'required|date_format:Y-m-d H:i:s',
            'updated_at'        => 'required|date_format:Y-m-d H:i:s',
            
            'agendamento_moto_id'   => 'required|integer|exists:config_motos,id',
            'agendamento_carro_id'  => 'required|integer|exists:config_carros,id',
        ];
    }
}
