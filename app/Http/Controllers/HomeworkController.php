<?php

namespace App\Http\Controllers;

use App\Models\Homework;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class HomeworkController extends Controller
{
    public function index()
    {
        $homeworks = Homework::where('user_id', Auth::id())
            ->latest()
            ->get()
            ->map(fn (Homework $hw) => $this->serialize($hw));

        return view('homework', ['homeworks' => $homeworks]);
    }

    public function create()
    {
        $subjects = Homework::where('user_id', Auth::id())
            ->select('subject')
            ->distinct()
            ->pluck('subject');

        return view('homeworkNew', ['subjects' => $subjects]);
    }

    public function list()
    {
        $homeworks = Homework::where('user_id', Auth::id())
            ->latest()
            ->get()
            ->map(fn (Homework $hw) => $this->serialize($hw));

        return response()->json(['homeworks' => $homeworks]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'subject'     => 'required|string|max:120',
            'description' => 'required|string|max:2000',
            'deadline'    => 'nullable|date',
            'file'        => 'nullable|file|max:20480',
        ], [
            'subject.required'     => 'Укажите предмет.',
            'description.required' => 'Опишите задание.',
            'deadline.date'        => 'Некорректная дата.',
            'file.max'             => 'Файл не должен превышать 20 МБ.',
        ]);

        $filePath = null;
        $fileName = null;

        if ($request->hasFile('file')) {
            $file = $request->file('file');
            $fileName = $file->getClientOriginalName();
            $ext = $file->getClientOriginalExtension();
            $safeName = Str::uuid()->toString() . ($ext ? '.' . $ext : '');
            $filePath = $file->storeAs('homework_files/' . Auth::id(), $safeName, 'local');
        }

        $hw = Homework::create([
            'user_id'     => Auth::id(),
            'subject'     => $validated['subject'],
            'description' => $validated['description'],
            'deadline'    => $validated['deadline'] ?? null,
            'file_path'   => $filePath,
            'file_name'   => $fileName,
        ]);

        return response()->json([
            'success'  => true,
            'homework' => $this->serialize($hw),
        ], 201);
    }

    public function download(Homework $homework)
    {
        if ($homework->user_id !== Auth::id()) {
            abort(403, 'Доступ запрещён');
        }

        if (!$homework->file_path || !Storage::disk('local')->exists($homework->file_path)) {
            abort(404, 'Файл не найден');
        }

        return Storage::disk('local')->download($homework->file_path, $homework->file_name);
    }

    public function destroy(Homework $homework)
    {
        if ($homework->user_id !== Auth::id()) {
            return response()->json(['error' => 'Доступ запрещён'], 403);
        }

        if ($homework->file_path && Storage::disk('local')->exists($homework->file_path)) {
            Storage::disk('local')->delete($homework->file_path);
        }

        $homework->delete();

        return response()->json(['success' => true]);
    }

    private function serialize(Homework $hw): array
    {
        return [
            'id'           => $hw->id,
            'subject'      => $hw->subject,
            'text'         => $hw->description,
            'deadline'     => $hw->deadline ? $hw->deadline->format('d.m.y') : null,
            'deadline_iso' => $hw->deadline ? $hw->deadline->format('Y-m-d') : null,
            'added'        => $hw->created_at ? $hw->created_at->getTimestampMs() : 0,
            'fileName'     => $hw->file_name,
            'downloadUrl'  => $hw->file_path ? route('homework.download', $hw) : null,
            'deleteUrl'    => route('homework.destroy', $hw),
        ];
    }
}
