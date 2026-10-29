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
            'target' => ['required', 'numeric', 'min:0.01'],
            'mitra_id' => ['required_without_all:id_sobat,candidates', 'nullable', 'integer', 'exists:mitras,id'],
            'id_sobat' => ['required_without_all:mitra_id,candidates', 'nullable', 'string', 'exists:mitras,id_sobat'],
            'candidates' => ['required_without_all:mitra_id,id_sobat', 'nullable', 'array', 'min:1'],
            'candidates.*' => ['required'],
        ];
    }

    public function messages(): array
    {
        return [
            'honor_id.required' => 'Parameter honor_id wajib diisi.',
            'honor_id.exists' => 'Honor dengan ID tersebut tidak ditemukan.',
            'mitra_id.required_without_all' => 'Parameter mitra_id, id_sobat, atau candidates wajib diisi.',
            'mitra_id.exists' => 'Mitra dengan ID tersebut tidak ditemukan.',
            'id_sobat.required_without_all' => 'Parameter id_sobat, mitra_id, atau candidates wajib diisi.',
            'id_sobat.exists' => 'Mitra dengan ID Sobat tersebut tidak ditemukan.',
            'candidates.required_without_all' => 'Parameter candidates, mitra_id, atau id_sobat wajib diisi.',
            'candidates.array' => 'Parameter candidates harus berupa array.',
            'candidates.min' => 'Parameter candidates minimal berisi 1 kandidat.',
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
