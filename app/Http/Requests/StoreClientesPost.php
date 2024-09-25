<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreClientesPost extends FormRequest
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
            'nome'          => 'required|string|max:500',
            'telefone'      => 'required|string|max:50',
            'email'         => 'required|string|max:50',
            'endereco'      => 'required|string|max:500',
            'token'         => 'required',
            'deleted'       => 'required|string',
            'created_at'    => 'required|date_format:Y-m-d H:i:s',
            'updated_at'    => 'required|date_format:Y-m-d H:i:s',
        ];
    }
}
