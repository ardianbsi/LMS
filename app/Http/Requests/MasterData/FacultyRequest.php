<?php

namespace App\Http\Requests\MasterData;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class FacultyRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create', \App\Models\Faculty::class)
            || $this->user()?->can('update', $this->route('faculty')) ?: false;
    }

    /**
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $facultyId = $this->route('faculty')?->id;

        return [
            'code' => ['required', 'string', 'max:32', Rule::unique('faculties', 'code')->ignore($facultyId)],
            'name' => ['required', 'string', 'max:255'],
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
            'code.required' => 'Kode fakultas wajib diisi.',
            'code.unique' => 'Kode fakultas sudah digunakan.',
            'name.required' => 'Nama fakultas wajib diisi.',
        ];
    }
}
