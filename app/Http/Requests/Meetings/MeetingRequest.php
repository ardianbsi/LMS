<?php

namespace App\Http\Requests\Meetings;

use App\Models\CourseClass;
use App\Models\Meeting;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class MeetingRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();

        if (! $user) {
            return false;
        }

        $meeting = $this->route('meeting');
        $courseClass = $this->route('courseClass');

        if ($meeting instanceof Meeting) {
            return $user->can('update', $meeting);
        }

        return $courseClass instanceof CourseClass && $user->can('manageMeetings', $courseClass);
    }

    /**
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $courseClass = $this->route('courseClass');
        $meetingId = $this->route('meeting')?->id;

        return [
            'meeting_number' => [
                'required',
                'integer',
                'min:1',
                'max:999',
                Rule::unique('meetings', 'meeting_number')
                    ->where(fn ($query) => $query->where('course_class_id', $courseClass->id))
                    ->ignore($meetingId),
            ],
            'title' => ['nullable', 'string', 'max:255'],
            'meeting_date' => ['nullable', 'date'],
            'start_time' => ['nullable', 'date_format:H:i,H:i:s'],
            'end_time' => ['nullable', 'date_format:H:i,H:i:s', 'after:start_time'],
            'topic' => ['nullable', 'string', 'max:1000'],
            'summary' => ['nullable', 'string', 'max:5000'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'meeting_number.required' => 'Nomor pertemuan wajib diisi.',
            'meeting_number.integer' => 'Nomor pertemuan harus berupa angka.',
            'meeting_number.min' => 'Nomor pertemuan minimal 1.',
            'meeting_number.unique' => 'Nomor pertemuan sudah digunakan pada kelas ini.',
            'end_time.after' => 'Jam selesai harus setelah jam mulai.',
        ];
    }
}
