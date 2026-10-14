<?php

namespace App\Http\Requests\MasterData;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class LecturerRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create', \App\Models\Lecturer::class) ?: false;
    }

    /**
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $id = $this->route('lecturer')?->id;
        $isUpdate = $this->isMethod('put') || $this->isMethod('patch');

        return [
            'name' => $isUpdate ? ['nullable', 'string', 'max:255'] : ['required', 'string', 'max:255'],
            'email' => $isUpdate
                ? ['nullable', 'email:filter', 'max:255', Rule::unique('users', 'email')->ignore($this->route('lecturer')?->user_id)]
                : ['required', 'email:filter', 'max:255', Rule::unique('users', 'email')],
            'study_program_id' => ['nullable', 'integer', 'exists:study_programs,id'],
            'nip' => ['required', 'string', 'max:32', Rule::unique('lecturers', 'nip')->ignore($id)],
            'academic_title' => ['nullable', 'string', 'max:128'],
            'phone' => ['nullable', 'string', 'max:32'],
            'status' => ['sometimes', 'string', 'max:32'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'name.required' => 'Nama dosen wajib diisi.',
            'email.required' => 'Email akun wajib diisi.',
            'email.unique' => 'Email sudah digunakan.',
            'nip.required' => 'NIP wajib diisi.',
            'nip.unique' => 'NIP sudah digunakan.',
        ];
    }
}
