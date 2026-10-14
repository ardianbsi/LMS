<?php

namespace App\Http\Requests\Classes;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CourseClassRequest extends FormRequest
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
        $id = $this->route('course_class')?->id ?? $this->route('class')?->id;

        return [
            'course_id' => ['required', 'integer', 'exists:courses,id'],
            'semester_id' => ['required', 'integer', 'exists:semesters,id'],
            'class_code' => [
                'required', 'string', 'max:16',
                Rule::unique('course_classes', 'class_code')->where(
                    fn ($q) => $q->where('course_id', $this->input('course_id'))->where('semester_id', $this->input('semester_id'))
                )->ignore($id),
            ],
            'name' => ['nullable', 'string', 'max:255'],
            'capacity' => ['nullable', 'integer', 'min:1', 'max:500'],
            'status' => ['sometimes', 'string', 'max:32'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'course_id.required' => 'Mata kuliah wajib dipilih.',
            'semester_id.required' => 'Semester wajib dipilih.',
            'class_code.required' => 'Kode kelas wajib diisi.',
            'class_code.unique' => 'Kode kelas sudah dipakai untuk MK + semester ini.',
        ];
    }
}
