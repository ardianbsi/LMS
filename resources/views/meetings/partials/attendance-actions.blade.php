@if (! $attendance)
    <form method="POST" action="{{ route('meetings.session.attendances.store', [$courseClass, $meeting]) }}" class="flex items-center justify-end gap-1">
        @csrf
        <input type="hidden" name="student_id" value="{{ $student->id }}">
        <select name="status" class="rounded-lg border border-slate-300 px-2 py-1 text-xs">
            @foreach (\App\Enums\AttendanceStatus::cases() as $option)
                <option value="{{ $option->value }}">{{ $option->label() }}</option>
            @endforeach
        </select>
        <button type="submit" class="rounded-lg bg-indigo-600 px-3 py-1.5 text-xs font-bold text-white hover:bg-indigo-500">Catat</button>
    </form>
@else
    <form method="POST" action="{{ route('meetings.session.attendances.correct', [$courseClass, $meeting, $attendance]) }}" class="flex items-center justify-end gap-1">
        @csrf
        @method('PUT')
        <input type="hidden" name="reason" value="Koreksi oleh {{ auth()->user()->name }}">
        <select name="status" class="rounded-lg border border-slate-300 px-2 py-1 text-xs">
            @foreach (\App\Enums\AttendanceStatus::cases() as $option)
                <option value="{{ $option->value }}" @selected($status === $option)>{{ $option->label() }}</option>
            @endforeach
        </select>
        <button type="submit" class="rounded-lg bg-amber-500 px-3 py-1.5 text-xs font-bold text-white hover:bg-amber-600">Koreksi</button>
    </form>
@endif
