<?php

namespace App\Http\Controllers;

use App\Models\Homework;
use App\Models\Note;
use App\Models\PersonalFile;
use App\Models\ScheduleLesson;
use App\Models\Task;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TaskController extends Controller
{
    /**
     * 1. Главная страница (задачи текущего пользователя)
     */
    public function index()
    {
        if (!Auth::check()) {
            return view('welcome');
        }

        $tasks = Task::where('user_id', Auth::id())
                     ->latest()
                     ->get();

        $notes = Note::where('user_id', Auth::id())
                     ->latest()
                     ->get();

        $files = PersonalFile::where('user_id', Auth::id())
                     ->latest()
                     ->take(10)
                     ->get();

        $homeworksData = Homework::where('user_id', Auth::id())
                     ->latest()
                     ->get()
                     ->map(fn (Homework $hw) => [
                         'id'       => $hw->id,
                         'subject'  => $hw->subject,
                         'text'     => $hw->description,
                         'deadline' => $hw->deadline ? $hw->deadline->format('d.m.y') : null,
                     ])
                     ->values();

        $scheduleData = ScheduleLesson::where('user_id', Auth::id())
                     ->orderBy('time_range')
                     ->get()
                     ->map(fn (ScheduleLesson $l) => [
                         'day_of_week' => (int) $l->day_of_week,
                         'time'        => $l->time_range,
                         'subject'     => $l->subject,
                         'classroom'   => $l->classroom ?: '—',
                         'teacher'     => $l->teacher ?: '—',
                     ])
                     ->values();

        return view('index', compact('tasks', 'notes', 'files', 'homeworksData', 'scheduleData'));
    }

    /**
     * 2. Отдельная страница задач (/task)
     */
    public function taskPage()
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        $tasks = Task::where('user_id', Auth::id())
                     ->latest()
                     ->get();

        return view('task', compact('tasks'));
    }

    /**
     * 3. Сохранение новой задачи
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'task_text' => 'required|string|max:255',
            'deadline'  => 'nullable|date',
        ]);

        $task = Task::create([
            'user_id'      => Auth::id(),
            'title'        => $validated['task_text'],
            'deadline'     => $validated['deadline'] ?? null,
            'status'       => 'todo',
            'is_completed' => false,
        ]);

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'task'    => [
                    'id'                 => $task->id,
                    'title'              => $task->title,
                    'deadline'           => $task->deadline ? $task->deadline->format('Y-m-d') : null,
                    'deadline_formatted' => $task->deadline ? $task->deadline->format('d.m.y') : null,
                    'status'             => $task->status,
                    'is_completed'       => $task->is_completed,
                ],
            ]);
        }

        return back();
    }

    /**
     * 4. Переключение статуса задачи галочкой (завершена / в очередь)
     */
    public function toggle(Task $task)
    {
        if ($task->user_id !== Auth::id()) {
            return response()->json(['error' => 'Доступ запрещён'], 403);
        }

        if ($task->status === 'completed' || $task->is_completed) {
            $task->status = 'todo';
            $task->is_completed = false;
        } else {
            $task->status = 'completed';
            $task->is_completed = true;
        }

        $task->save();

        return response()->json([
            'success'      => true,
            'status'       => $task->status,
            'is_completed' => $task->is_completed,
        ]);
    }

    /**
     * 5. Обновление статуса задачи (todo, in_progress, completed)
     */
    public function updateStatus(Request $request, Task $task)
    {
        if ($task->user_id !== Auth::id()) {
            return response()->json(['error' => 'Доступ запрещён'], 403);
        }

        $validated = $request->validate([
            'status' => 'required|in:todo,in_progress,completed',
        ]);

        $status = $validated['status'];
        $task->status = $status;
        $task->is_completed = ($status === 'completed');
        $task->save();

        return response()->json([
            'success'      => true,
            'status'       => $task->status,
            'is_completed' => $task->is_completed,
        ]);
    }

    /**
     * 6. Удаление задачи
     */
    public function destroy(Task $task)
    {
        if ($task->user_id !== Auth::id()) {
            return response()->json(['error' => 'Доступ запрещён'], 403);
        }

        $task->delete();

        if (request()->wantsJson() || request()->ajax()) {
            return response()->json(['success' => true]);
        }

        return back();
    }
}
