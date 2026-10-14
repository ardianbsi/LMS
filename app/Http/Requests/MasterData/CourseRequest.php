<?php

namespace App\Http\Requests\MasterData;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CourseRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create', \App\Models\Course::class) ?: false;
    }

    /**
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $id = $this->route('course')?->id;

        return [
            'study_program_id' => ['required', 'integer', 'exists:study_programs,id'],
            'code' => ['required', 'string', 'max:32', Rule::unique('courses', 'code')->ignore($id)],
            'name' => ['required', 'string', 'max:255'],
            'credits' => ['required', 'integer', 'min:1', 'max:12'],
            'semester_number' => ['nullable', 'integer', 'min:1', 'max:14'],
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
            'study_program_id.required' => 'Program studi wajib dipilih.',
            'code.required' => 'Kode mata kuliah wajib diisi.',
            'code.unique' => 'Kode mata kuliah sudah digunakan.',
            'name.required' => 'Nama mata kuliah wajib diisi.',
            'credits.required' => 'SKS wajib diisi.',
        ];
    }
}
