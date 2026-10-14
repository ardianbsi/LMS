<?php

namespace App\Http\Requests\Classes;

use Illuminate\Foundation\Http\FormRequest;

class ClassLecturerRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create', \App\Models\CourseClass::class) ?: false;
    }

    /**
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'lecturer_id' => ['required', 'integer', 'exists:lecturers,id'],
            'role' => ['sometimes', 'string', 'max:32'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'lecturer_id.required' => 'Dosen wajib dipilih.',
            'lecturer_id.exists' => 'Dosen tidak valid.',
        ];
    }
}
