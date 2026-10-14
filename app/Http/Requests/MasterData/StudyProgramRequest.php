<?php

namespace App\Http\Requests\MasterData;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StudyProgramRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create', \App\Models\StudyProgram::class) ?: false;
    }

    /**
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $programId = $this->route('studyProgram')?->id ?? $this->route('study_program')?->id ?? $this->route('program')?->id;

        return [
            'faculty_id' => ['required', 'integer', 'exists:faculties,id'],
            'code' => ['required', 'string', 'max:32', Rule::unique('study_programs', 'code')->ignore($programId)],
            'name' => ['required', 'string', 'max:255'],
            'degree' => ['nullable', 'string', 'max:16'],
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
            'faculty_id.required' => 'Fakultas wajib dipilih.',
            'faculty_id.exists' => 'Fakultas tidak valid.',
            'code.required' => 'Kode prodi wajib diisi.',
            'code.unique' => 'Kode prodi sudah digunakan.',
            'name.required' => 'Nama prodi wajib diisi.',
        ];
    }
}
