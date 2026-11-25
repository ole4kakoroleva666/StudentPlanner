<?php

namespace App\Http\Controllers;

use App\Models\Task;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{
    /**
     * Возвращает список уведомлений по ближайшим и просроченным дедлайнам задач.
     */
    public function index()
    {
        $today = Carbon::today();
        $tomorrow = Carbon::tomorrow();

        $tasks = Task::where('user_id', Auth::id())
            ->where('is_completed', false)
            ->whereNotNull('deadline')
            ->whereDate('deadline', '<=', $tomorrow)
            ->orderBy('deadline')
            ->limit(15)
            ->get();

        $notifications = $tasks->map(function (Task $task) use ($today, $tomorrow) {
            $deadline = Carbon::parse($task->deadline)->startOfDay();

            if ($deadline->lt($today)) {
                $status = 'overdue';
                $label = 'Просрочено';
            } elseif ($deadline->equalTo($today)) {
                $status = 'today';
                $label = 'Дедлайн сегодня';
            } else {
                $status = 'tomorrow';
                $label = 'Дедлайн завтра';
            }

            return [
                'key'      => $task->id . '_' . $deadline->format('Y-m-d'),
                'title'    => $task->title,
                'label'    => $label,
                'status'   => $status,
                'deadline' => $deadline->format('d.m.Y'),
                'url'      => '/task',
            ];
        })->values();

        return response()->json([
            'notifications' => $notifications,
        ]);
    }
}
