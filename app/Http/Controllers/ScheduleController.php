<?php

namespace App\Http\Controllers;

use App\Models\ScheduleLesson;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ScheduleController extends Controller
{
    public function index()
    {
        $lessons = ScheduleLesson::where('user_id', Auth::id())
            ->orderBy('day_of_week')
            ->orderBy('time_range')
            ->get()
            ->map(fn (ScheduleLesson $l) => $this->serialize($l));

        return view('shedule', ['lessons' => $lessons]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'day_of_week' => 'required|integer|min:1|max:6',
            'time_range'  => 'required|string|max:50',
            'subject'     => 'required|string|max:150',
            'classroom'   => 'nullable|string|max:50',
            'teacher'     => 'nullable|string|max:120',
        ], [
            'day_of_week.required' => 'Выберите день недели.',
            'time_range.required'  => 'Укажите время пары.',
            'subject.required'     => 'Укажите предмет.',
        ]);

        $lesson = ScheduleLesson::create([
            'user_id'     => Auth::id(),
            'day_of_week' => $validated['day_of_week'],
            'time_range'  => $validated['time_range'],
            'subject'     => $validated['subject'],
            'classroom'   => $validated['classroom'] ?? null,
            'teacher'     => $validated['teacher'] ?? null,
        ]);

        return response()->json([
            'success' => true,
            'lesson'  => $this->serialize($lesson),
        ], 201);
    }

    public function destroy(ScheduleLesson $lesson)
    {
        if ($lesson->user_id !== Auth::id()) {
            return response()->json(['error' => 'Доступ запрещён'], 403);
        }

        $lesson->delete();

        return response()->json(['success' => true]);
    }

    private function serialize(ScheduleLesson $lesson): array
    {
        return [
            'id'          => $lesson->id,
            'day_of_week' => (int) $lesson->day_of_week,
            'time'        => $lesson->time_range,
            'subject'     => $lesson->subject,
            'classroom'   => $lesson->classroom ?: '—',
            'teacher'     => $lesson->teacher ?: '—',
            'deleteUrl'   => route('schedule.destroy', $lesson),
        ];
    }
}
