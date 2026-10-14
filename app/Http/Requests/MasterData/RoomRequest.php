<?php

namespace App\Http\Requests\MasterData;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class RoomRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create', \App\Models\Room::class) ?: false;
    }

    /**
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $id = $this->route('room')?->id;

        return [
            'code' => ['required', 'string', 'max:32', Rule::unique('rooms', 'code')->ignore($id)],
            'name' => ['required', 'string', 'max:255'],
            'building' => ['nullable', 'string', 'max:128'],
            'capacity' => ['nullable', 'integer', 'min:1', 'max:1000'],
            'description' => ['nullable', 'string'],
            'is_active' => ['sometimes', 'boolean'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'code.required' => 'Kode ruang wajib diisi.',
            'code.unique' => 'Kode ruang sudah digunakan.',
            'name.required' => 'Nama ruang wajib diisi.',
        ];
    }
}
