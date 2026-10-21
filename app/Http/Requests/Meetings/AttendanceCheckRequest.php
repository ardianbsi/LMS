<?php

namespace App\Http\Requests\Meetings;

use App\Enums\AttendanceStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AttendanceCheckRequest extends FormRequest
{
    public function authorize(): bool
    {
        $meeting = $this->route('meeting');

        return $meeting !== null && ($this->user()?->can('mark', $meeting) ?: false);
    }

    /**
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'student_id' => ['required', 'integer', 'exists:students,id'],
            'status' => ['required', Rule::enum(AttendanceStatus::class)],
            'note' => ['nullable', 'string', 'max:500'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'student_id.required' => 'Mahasiswa wajib dipilih.',
            'student_id.exists' => 'Mahasiswa tidak valid.',
            'status.required' => 'Status kehadiran wajib dipilih.',
            'status.enum' => 'Status kehadiran tidak valid.',
        ];
    }
}
