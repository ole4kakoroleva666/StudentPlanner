<?php

namespace App\Http\Controllers;

use App\Models\PersonalFile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class PersonalFileController extends Controller
{
    /**
     * Страница личных файлов (/privateFiels)
     */
    public function index()
    {
        $files = PersonalFile::where('user_id', Auth::id())
            ->latest()
            ->paginate(10);

        if ($files->currentPage() > $files->lastPage() && $files->lastPage() > 0) {
            return redirect()->route('private-files.index', ['page' => $files->lastPage()]);
        }

        return view('privateFiels', compact('files'));
    }

    /**
     * Загрузка нового личного файла
     */
    public function store(Request $request)
    {
        $request->validate([
            'file' => 'required|file|max:20480', // до 20 МБ
        ], [
            'file.required' => 'Выберите файл для загрузки.',
            'file.max' => 'Размер файла не должен превышать 20 МБ.',
        ]);

        $uploaded = $request->file('file');
        $originalName = $uploaded->getClientOriginalName();
        $extension = $uploaded->getClientOriginalExtension();
        $safeFilename = Str::uuid()->toString() . ($extension ? '.' . $extension : '');
        $directory = 'private_files/' . Auth::id();

        $storedPath = $uploaded->storeAs($directory, $safeFilename, 'local');

        $personalFile = PersonalFile::create([
            'user_id'       => Auth::id(),
            'original_name' => $originalName,
            'stored_path'   => $storedPath,
            'size'          => $uploaded->getSize() ?: 0,
            'mime_type'     => $uploaded->getClientMimeType(),
        ]);

        return response()->json([
            'success' => true,
            'file'    => $this->serializeFile($personalFile),
        ]);
    }

    /**
     * Скачивание личного файла
     */
    public function download(PersonalFile $file)
    {
        if ($file->user_id !== Auth::id()) {
            abort(403, 'Доступ запрещён');
        }

        if (!Storage::disk('local')->exists($file->stored_path)) {
            abort(404, 'Файл не найден');
        }

        return Storage::disk('local')->download($file->stored_path, $file->original_name);
    }

    /**
     * Удаление личного файла
     */
    public function destroy(PersonalFile $file)
    {
        if ($file->user_id !== Auth::id()) {
            return response()->json(['error' => 'Доступ запрещён'], 403);
        }

        if ($file->stored_path && Storage::disk('local')->exists($file->stored_path)) {
            Storage::disk('local')->delete($file->stored_path);
        }

        $file->delete();

        return response()->json(['success' => true]);
    }

    private function serializeFile(PersonalFile $file): array
    {
        return [
            'id'                   => $file->id,
            'original_name'        => $file->original_name,
            'created_at_formatted' => $file->created_at?->format('d.m.y') ?? '',
            'size'                 => $file->size,
            'download_url'         => route('private-files.download', $file),
            'delete_url'           => route('private-files.destroy', $file),
        ];
    }
}
