<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreConfigMotosPost extends FormRequest
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
            'modelo'        => 'required|string|max:500',
            'nome'          => 'required|string|max:500',
            'marca'         => 'required|string|max:500',
            'nome_dono'     => 'required|string|max:500',
            'cor'           => 'required|string|max:50',
            'status'        => 'required|boolean',
            'token'         => 'required',
            'anexo'         => 'required|string',
            'observacao'    => 'required|text|max:500',
            'deleted'       => 'required|string',
            'ano_fabricacao'=> 'required',
            'quilometragem' => 'required|string',
            'garantia'      => 'required|string',
            'created_at'    => 'required|date_format:Y-m-d H:i:s',
            'updated_at'    => 'required|date_format:Y-m-d H:i:s',
        ];
    }
}
