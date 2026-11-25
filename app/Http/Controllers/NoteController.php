<?php

namespace App\Http\Controllers;

use App\Models\Note;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class NoteController extends Controller
{
    /**
     * Страница заметок (/notes)
     */
    public function index()
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        $notes = Note::where('user_id', Auth::id())
            ->latest()
            ->get();

        $pinnedNotes = $notes->where('is_pinned', true)->values();
        $otherNotes = $notes->where('is_pinned', false)->values();

        return view('notes', compact('pinnedNotes', 'otherNotes'));
    }

    /**
     * Создание новой заметки
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title'      => 'nullable|string|max:255',
            'content'    => 'nullable|string|max:65535',
            'is_pinned'  => 'nullable|boolean',
            'attachment' => 'nullable|file|max:10240', // до 10 МБ
        ]);

        $title = trim((string) ($validated['title'] ?? ''));
        $content = $this->sanitizeHtml((string) ($validated['content'] ?? ''));
        $plainText = trim(strip_tags($content));

        if ($title === '' && $plainText === '' && !$request->hasFile('attachment')) {
            return response()->json([
                'success' => false,
                'message' => 'Заметка не может быть пустой.',
            ], 422);
        }

        $attachmentPath = null;
        $attachmentName = null;

        if ($request->hasFile('attachment')) {
            [$attachmentPath, $attachmentName] = $this->saveAttachment($request->file('attachment'));
        }

        $note = Note::create([
            'user_id'         => Auth::id(),
            'title'           => $title !== '' ? $title : null,
            'content'         => $content,
            'is_pinned'       => $request->boolean('is_pinned'),
            'attachment_path' => $attachmentPath,
            'attachment_name' => $attachmentName,
        ]);

        return response()->json([
            'success' => true,
            'note'    => $this->serializeNote($note),
        ]);
    }

    /**
     * Обновление заметки
     */
    public function update(Request $request, Note $note)
    {
        if ($note->user_id !== Auth::id()) {
            return response()->json(['error' => 'Доступ запрещён'], 403);
        }

        $validated = $request->validate([
            'title'             => 'nullable|string|max:255',
            'content'           => 'nullable|string|max:65535',
            'is_pinned'         => 'nullable|boolean',
            'attachment'        => 'nullable|file|max:10240',
            'remove_attachment' => 'nullable|boolean',
        ]);

        $title = trim((string) ($validated['title'] ?? ''));
        $content = $this->sanitizeHtml((string) ($validated['content'] ?? ''));

        if ($request->boolean('remove_attachment') && $note->attachment_path) {
            $this->deleteAttachmentFile($note->attachment_path);
            $note->attachment_path = null;
            $note->attachment_name = null;
        }

        if ($request->hasFile('attachment')) {
            if ($note->attachment_path) {
                $this->deleteAttachmentFile($note->attachment_path);
            }
            [$attachmentPath, $attachmentName] = $this->saveAttachment($request->file('attachment'));
            $note->attachment_path = $attachmentPath;
            $note->attachment_name = $attachmentName;
        }

        $note->title = $title !== '' ? $title : null;
        $note->content = $content;
        if ($request->has('is_pinned')) {
            $note->is_pinned = $request->boolean('is_pinned');
        }
        $note->save();

        return response()->json([
            'success' => true,
            'note'    => $this->serializeNote($note),
        ]);
    }

    /**
     * Переключение закрепления (булавка)
     */
    public function togglePin(Note $note)
    {
        if ($note->user_id !== Auth::id()) {
            return response()->json(['error' => 'Доступ запрещён'], 403);
        }

        $note->is_pinned = !$note->is_pinned;
        $note->save();

        return response()->json([
            'success'   => true,
            'is_pinned' => $note->is_pinned,
            'note'      => $this->serializeNote($note),
        ]);
    }

    /**
     * Удаление заметки
     */
    public function destroy(Note $note)
    {
        if ($note->user_id !== Auth::id()) {
            return response()->json(['error' => 'Доступ запрещён'], 403);
        }

        if ($note->attachment_path) {
            $this->deleteAttachmentFile($note->attachment_path);
        }

        $note->delete();

        return response()->json(['success' => true]);
    }

    /**
     * Очистка HTML от опасных тегов и атрибутов, сохраняя базовое форматирование.
     */
    private function sanitizeHtml(string $html): string
    {
        $allowedTags = '<b><strong><i><em><u><h1><h2><p><br><ul><ol><li><div><span>';
        $cleaned = strip_tags($html, $allowedTags);

        // Удаляем inline-обработчики событий (onclick, onerror и т.д.) и javascript:-ссылки
        $cleaned = preg_replace('/\s+on[a-z]+\s*=\s*(?:"[^"]*"|\'[^\']*\'|[^\s>]+)/iu', '', $cleaned) ?? $cleaned;
        $cleaned = preg_replace('/\s+style\s*=\s*(?:"[^"]*"|\'[^\']*\')/iu', '', $cleaned) ?? $cleaned;

        return trim($cleaned);
    }

    /**
     * Сохраняет прикреплённый файл в public/uploads/notes.
     */
    private function saveAttachment($file): array
    {
        $dir = public_path('uploads/notes');
        if (!File::exists($dir)) {
            File::makeDirectory($dir, 0755, true);
        }

        $originalName = $file->getClientOriginalName();
        $extension = $file->getClientOriginalExtension();
        $safeFilename = Str::uuid()->toString() . ($extension ? '.' . $extension : '');
        $file->move($dir, $safeFilename);

        return ['uploads/notes/' . $safeFilename, $originalName];
    }

    private function deleteAttachmentFile(string $relativePath): void
    {
        $fullPath = public_path($relativePath);
        if (File::exists($fullPath)) {
            File::delete($fullPath);
        }
    }

    private function serializeNote(Note $note): array
    {
        return [
            'id'              => $note->id,
            'title'           => $note->title,
            'content'         => $note->content,
            'is_pinned'       => (bool) $note->is_pinned,
            'attachment_url'  => $note->attachment_path ? asset($note->attachment_path) : null,
            'attachment_name' => $note->attachment_name,
            'updated_at'      => $note->updated_at?->toIso8601String(),
        ];
    }
}
