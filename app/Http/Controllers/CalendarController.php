<?php

namespace App\Http\Controllers;

use App\Models\DayNote;
use App\Models\Homework;
use App\Models\ScheduleLesson;
use App\Models\Task;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CalendarController extends Controller
{
    public function index()
    {
        return view('calendar');
    }

    public function oneDay()
    {
        return view('calendarOneDay');
    }

    /**
     * События на месяц: GET /calendar/events?year=2026&month=11
     */
    public function events(Request $request)
    {
        $year = (int) $request->query('year', now()->year);
        $month = (int) $request->query('month', now()->month);

        $start = Carbon::createFromDate($year, $month, 1)->startOfDay();
        $end = $start->copy()->endOfMonth()->endOfDay();

        $userId = Auth::id();

        $lessonsByDay = ScheduleLesson::where('user_id', $userId)
            ->get()
            ->groupBy('day_of_week');

        $homeworksByDate = Homework::where('user_id', $userId)
            ->whereBetween('deadline', [$start->toDateString(), $end->toDateString()])
            ->get()
            ->groupBy(fn (Homework $hw) => $hw->deadline->format('Y-m-d'));

        $notesByDate = DayNote::where('user_id', $userId)
            ->whereBetween('date', [$start->toDateString(), $end->toDateString()])
            ->get()
            ->groupBy(fn (DayNote $n) => $n->date->format('Y-m-d'));

        $tasksByDate = Task::where('user_id', $userId)
            ->whereBetween('deadline', [$start->toDateString(), $end->toDateString()])
            ->get()
            ->groupBy(fn (Task $t) => $t->deadline->format('Y-m-d'));

        $result = [];
        for ($d = 1; $d <= $start->daysInMonth; $d++) {
            $date = Carbon::createFromDate($year, $month, $d);
            $dateStr = $date->format('Y-m-d');
            // В Carbon dayOfWeekIso: 1 = Пн .. 7 = Вс
            $isoDay = $date->dayOfWeekIso;

            $lessonsCount = $isoDay <= 6 ? 4 : 0;
            $hwCount = $homeworksByDate->get($dateStr)?->count() ?? 0;
            $notesCount = $notesByDate->get($dateStr)?->count() ?? 0;
            $tasksCount = $tasksByDate->get($dateStr)?->count() ?? 0;

            $result[$dateStr] = [
                'lessons'  => $lessonsCount,
                'homework' => $hwCount + $notesCount + $tasksCount,
            ];
        }

        return response()->json($result);
    }

    /**
     * Данные конкретного дня: GET /calendar/day?date=YYYY-MM-DD
     */
    public function dayData(Request $request)
    {
        $dateStr = $request->query('date', now()->toDateString());
        $date = Carbon::parse($dateStr);
        $userId = Auth::id();
        $isoDay = $date->dayOfWeekIso;

        $lessons = $isoDay <= 6
            ? collect([
                ['time' => '08:30 — 10:05', 'subject' => 'Математический анализ', 'classroom' => '2-204', 'teacher' => 'Иванова Ольга Сергеевна'],
                ['time' => '10:15 — 11:50', 'subject' => 'Проектирование и разработка интерфейсов', 'classroom' => '1-306', 'teacher' => 'Гунько Григорий Александрович'],
                ['time' => '12:30 — 14:05', 'subject' => 'Базы данных', 'classroom' => '3-102', 'teacher' => 'Смирнов Алексей Викторович'],
                ['time' => '14:15 — 15:50', 'subject' => 'Компьютерные сети', 'classroom' => '2-110', 'teacher' => 'Кузнецов Дмитрий Павлович'],
            ])
            : collect();

        $homeworks = Homework::where('user_id', $userId)
            ->whereDate('deadline', $date->toDateString())
            ->get()
            ->map(fn (Homework $hw) => [
                'id'      => $hw->id,
                'subject' => $hw->subject,
                'text'    => $hw->description,
            ]);

        $dayNotes = DayNote::where('user_id', $userId)
            ->whereDate('date', $date->toDateString())
            ->orderBy('id')
            ->get()
            ->map(fn (DayNote $n) => [
                'id'        => $n->id,
                'text'      => $n->content,
                'completed' => (bool) $n->is_completed,
            ]);

        return response()->json([
            'lessons'   => $lessons->values(),
            'homeworks' => $homeworks->values(),
            'notes'     => $dayNotes->values(),
        ]);
    }

    public function storeDayNote(Request $request)
    {
        $validated = $request->validate([
            'date'    => 'required|date',
            'content' => 'required|string|max:255',
        ]);

        $note = DayNote::create([
            'user_id'      => Auth::id(),
            'date'         => $validated['date'],
            'content'      => $validated['content'],
            'is_completed' => false,
        ]);

        return response()->json([
            'success' => true,
            'note'    => [
                'id'        => $note->id,
                'text'      => $note->content,
                'completed' => false,
            ],
        ], 201);
    }

    public function toggleDayNote(DayNote $dayNote)
    {
        if ($dayNote->user_id !== Auth::id()) {
            return response()->json(['error' => 'Доступ запрещён'], 403);
        }

        $dayNote->update(['is_completed' => !$dayNote->is_completed]);

        return response()->json([
            'success'      => true,
            'is_completed' => (bool) $dayNote->is_completed,
        ]);
    }

    public function destroyDayNote(DayNote $dayNote)
    {
        if ($dayNote->user_id !== Auth::id()) {
            return response()->json(['error' => 'Доступ запрещён'], 403);
        }

        $dayNote->delete();

        return response()->json(['success' => true]);
    }
}
