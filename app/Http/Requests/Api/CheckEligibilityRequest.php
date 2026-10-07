<?php

namespace App\Http\Requests\Api;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class CheckEligibilityRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'honor_id' => ['required', 'string', 'exists:honors,id'],
            'mitra_id' => ['required_without:id_sobat', 'nullable', 'integer', 'exists:mitras,id'],
            'id_sobat' => ['required_without:mitra_id', 'nullable', 'string', 'exists:mitras,id_sobat'],
            'target' => ['required', 'numeric', 'min:0.01'],
        ];
    }

    public function messages(): array
    {
        return [
            'honor_id.required' => 'Parameter honor_id wajib diisi.',
            'honor_id.exists' => 'Honor dengan ID tersebut tidak ditemukan.',
            'mitra_id.required_without' => 'Parameter mitra_id atau id_sobat wajib diisi.',
            'mitra_id.exists' => 'Mitra dengan ID tersebut tidak ditemukan.',
            'id_sobat.required_without' => 'Parameter id_sobat atau mitra_id wajib diisi.',
            'id_sobat.exists' => 'Mitra dengan ID Sobat tersebut tidak ditemukan.',
            'target.required' => 'Parameter target wajib diisi.',
            'target.numeric' => 'Target harus berupa angka desimal atau bulat.',
            'target.min' => 'Target minimal bernilai 0.01.',
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(response()->json([
            'status' => 'error',
            'message' => 'Parameter permintaan tidak valid.',
            'errors' => $validator->errors(),
        ], 422));
    }
}
