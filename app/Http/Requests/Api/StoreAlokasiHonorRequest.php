<?php

namespace App\Http\Requests\Api;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class StoreAlokasiHonorRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        if ($this->has('allocations')) {
            return [
                'allocations' => ['required', 'array', 'min:1'],
                'allocations.*.honor_id' => ['required', 'string', 'exists:honors,id'],
                'allocations.*.mitra_id' => ['required_without:allocations.*.id_sobat', 'nullable', 'integer', 'exists:mitras,id'],
                'allocations.*.id_sobat' => ['required_without:allocations.*.mitra_id', 'nullable', 'string', 'exists:mitras,id_sobat'],
                'allocations.*.target' => ['required', 'numeric', 'min:0.01'],
            ];
        }

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
            'target.numeric' => 'Target harus berupa angka.',
            'target.min' => 'Target minimal 0.01.',
            'allocations.required' => 'Daftar alokasi wajib diisi.',
            'allocations.array' => 'Parameter allocations harus berupa array.',
            'allocations.min' => 'Minimal 1 item alokasi diperlukan.',
            'allocations.*.honor_id.required' => 'Item alokasi harus memiliki honor_id.',
            'allocations.*.honor_id.exists' => 'Honor ID pada item alokasi tidak ditemukan.',
            'allocations.*.target.required' => 'Item alokasi harus memiliki target.',
            'allocations.*.target.min' => 'Target pada item alokasi minimal 0.01.',
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(response()->json([
            'status' => 'error',
            'message' => 'Parameter alokasi tidak valid.',
            'errors' => $validator->errors(),
        ], 422));
    }
}
