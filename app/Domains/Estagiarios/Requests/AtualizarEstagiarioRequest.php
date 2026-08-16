<?php

namespace App\Domains\Estagiarios\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class AtualizarEstagiarioRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
        'data' => 'required|date',
        'entrada' => 'nullable',
        'saida' => 'nullable',
        'cpf' => 'required|string|max:14|unique:estagiarios,cpf,' . $this->route('id'), 
        'nome' => 'required',
        'setor' => 'required',
        'motivo' => 'nullable|string',
        'observacao' => 'nullable|string'
    ];
    }
}
