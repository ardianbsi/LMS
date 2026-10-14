<?php

namespace App\Http\Requests\MasterData;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SemesterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create', \App\Models\Semester::class) ?: false;
    }

    /**
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $id = $this->route('semester')?->id;

        return [
            'academic_year_id' => ['required', 'integer', 'exists:academic_years,id'],
            'term_code' => ['required', 'string', 'max:16', Rule::unique('semesters', 'term_code')->ignore($id)],
            'name' => ['required', 'string', 'max:64'],
            'start_date' => ['required', 'date'],
            'end_date' => ['required', 'date', 'after:start_date'],
            'is_active' => ['sometimes', 'boolean'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'academic_year_id.required' => 'Tahun akademik wajib dipilih.',
            'term_code.required' => 'Kode term wajib diisi.',
            'term_code.unique' => 'Kode term sudah digunakan.',
            'name.required' => 'Nama semester wajib diisi.',
            'end_date.after' => 'Tanggal selesai harus setelah tanggal mulai.',
        ];
    }
}
