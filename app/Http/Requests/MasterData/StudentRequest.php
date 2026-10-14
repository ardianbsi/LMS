<?php

namespace App\Http\Requests\MasterData;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StudentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create', \App\Models\Student::class) ?: false;
    }

    /**
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $id = $this->route('student')?->id;
        $isUpdate = $this->isMethod('put') || $this->isMethod('patch');

        return [
            'name' => $isUpdate ? ['nullable', 'string', 'max:255'] : ['required', 'string', 'max:255'],
            'email' => $isUpdate
                ? ['nullable', 'email:filter', 'max:255', Rule::unique('users', 'email')->ignore($this->route('student')?->user_id)]
                : ['required', 'email:filter', 'max:255', Rule::unique('users', 'email')],
            'study_program_id' => ['nullable', 'integer', 'exists:study_programs,id'],
            'nim' => ['required', 'string', 'max:32', Rule::unique('students', 'nim')->ignore($id)],
            'entry_year' => ['required', 'integer', 'min:2000', 'max:2100'],
            'phone' => ['nullable', 'string', 'max:32'],
            'address' => ['nullable', 'string'],
            'status' => ['sometimes', 'string', 'max:32'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'name.required' => 'Nama mahasiswa wajib diisi.',
            'email.required' => 'Email akun wajib diisi.',
            'email.unique' => 'Email sudah digunakan.',
            'nim.required' => 'NIM wajib diisi.',
            'nim.unique' => 'NIM sudah digunakan.',
            'entry_year.required' => 'Angkatan wajib diisi.',
        ];
    }
}
