<?php

namespace App\Http\Requests\Api;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class GetDokumenRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'tahun' => ['nullable', 'integer', 'min:2020', 'max:2050'],
            'bulan' => ['nullable', 'integer', 'min:1', 'max:12'],
            'id_kegiatan_manmit' => ['nullable', 'integer', 'exists:kegiatan_manmits,id'],
            'mitra_id' => ['nullable', 'integer', 'exists:mitras,id'],
            'id_honor' => ['nullable', 'string', 'exists:honors,id'],
            'full' => ['nullable', 'boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'tahun.integer' => 'Tahun harus berupa angka.',
            'bulan.min' => 'Bulan minimal 1.',
            'bulan.max' => 'Bulan maksimal 12.',
            'id_kegiatan_manmit.exists' => 'Kegiatan Manmit tidak ditemukan.',
            'mitra_id.exists' => 'Mitra tidak ditemukan.',
            'id_honor.exists' => 'Honor tidak ditemukan.',
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(response()->json([
            'status' => 'error',
            'message' => 'Parameter filter dokumen tidak valid.',
            'errors' => $validator->errors(),
        ], 422));
    }
}
