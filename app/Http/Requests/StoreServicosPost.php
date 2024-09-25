<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreServicosPost extends FormRequest
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
            
            'descricao'         => 'required',
            'preco'             => 'required|decimal',
            'tempo_estimado'    => 'required|string',
            'token'             => 'required',
            'deleted'           => 'required|string',
            'created_at'        => 'required|date_format:Y-m-d H:i:s',
            'updated_at'        => 'required|date_format:Y-m-d H:i:s',

        ];
    }
}
