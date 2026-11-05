<?php

namespace App\Http\Controllers;

use App\Models\Task;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class TaskController extends Controller
{
    /**
     * 1. Главная страница (выводим задачи текущего пользователя)
     */
    public function index()
    {
        // Если пользователь не вошёл — показываем лендинг welcome
        if (!Auth::check()) {
            return view('welcome');
        }

        // Берём из базы задачи авторизованного студента, свежие сверху
        $tasks = Task::where('user_id', Auth::id())
                     ->latest()
                     ->get();

        // Передаём список $tasks в шаблон index.blade.php
        return view('index', compact('tasks'));
    }

    /**
     * 2. Отдельная страница задач (/task)
     */
    public function taskPage()
    {
        // Страница доступна авторизованным пользователям
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        $tasks = Task::where('user_id', Auth::id())
                     ->latest()
                     ->get();

        return view('task', compact('tasks'));
    }

    /**
     * 3. Сохранение новой задачи (получаем task_text и опциональный deadline)
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
            'is_completed' => false,
        ]);

        // Если запрос отправлен через JavaScript (fetch / AJAX)
        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'task'    => [
                    'id'                 => $task->id,
                    'title'              => $task->title,
                    'deadline'           => $task->deadline ? $task->deadline->format('Y-m-d') : null,
                    'deadline_formatted' => $task->deadline ? $task->deadline->format('d.m.y') : null,
                    'is_completed'       => $task->is_completed,
                ],
            ]);
        }

        // Если обычная форма — возвращаем назад на страницу
        return back();
    }

    /**
     * 4. Переключение статуса задачи (выполнена / не выполнена)
     */
    public function toggle(Task $task)
    {
        if ($task->user_id !== Auth::id()) {
            return response()->json(['error' => 'Доступ запрещён'], 403);
        }

        $task->is_completed = !$task->is_completed;
        $task->save();

        return response()->json([
            'success'      => true,
            'is_completed' => $task->is_completed,
        ]);
    }

    /**
     * 5. Удаление задачи
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
