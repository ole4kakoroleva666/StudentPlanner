<?php

namespace App\Http\Controllers;

use App\Models\Note;
use App\Models\PersonalFile;
use App\Models\Task;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class ProfileController extends Controller
{
    /**
     * Страница профиля пользователя (/profile)
     */
    public function index()
    {
        $user = Auth::user();

        $stats = [
            'tasks_total'     => Task::where('user_id', $user->id)->count(),
            'tasks_completed' => Task::where('user_id', $user->id)->where('is_completed', true)->count(),
            'notes_count'     => Note::where('user_id', $user->id)->count(),
            'files_count'     => PersonalFile::where('user_id', $user->id)->count(),
        ];

        return view('profile', compact('user', 'stats'));
    }

    /**
     * Загрузка или обновление аватарки пользователя
     */
    public function updateAvatar(Request $request)
    {
        $request->validate([
            'avatar' => 'required|image|mimes:jpg,jpeg,png,webp,gif|max:5120',
        ], [
            'avatar.required' => 'Выберите изображение для аватарки.',
            'avatar.image'    => 'Файл должен быть изображением.',
            'avatar.mimes'    => 'Допустимые форматы: JPG, PNG, WEBP, GIF.',
            'avatar.max'      => 'Размер изображения не должен превышать 5 МБ.',
        ]);

        $user = Auth::user();
        $file = $request->file('avatar');
        $extension = $file->getClientOriginalExtension() ?: 'png';
        $filename = Str::uuid()->toString() . '.' . $extension;
        $directory = 'avatars/' . $user->id;

        if ($user->avatar && Storage::disk('public')->exists($user->avatar)) {
            Storage::disk('public')->delete($user->avatar);
        }

        $storedPath = $file->storeAs($directory, $filename, 'public');
        $user->update(['avatar' => $storedPath]);

        return response()->json([
            'success'    => true,
            'avatar_url' => $user->avatar_url,
            'has_avatar' => true,
            'message'    => 'Аватарка обновлена',
        ]);
    }

    /**
     * Удаление аватарки (возврат к стандартной)
     */
    public function deleteAvatar()
    {
        $user = Auth::user();

        if ($user->avatar && Storage::disk('public')->exists($user->avatar)) {
            Storage::disk('public')->delete($user->avatar);
        }

        $user->update(['avatar' => null]);

        return response()->json([
            'success'    => true,
            'avatar_url' => $user->avatar_url,
            'has_avatar' => false,
            'message'    => 'Аватарка удалена',
        ]);
    }

    /**
     * Обновление основных данных профиля (username, email, study_group, bio)
     */
    public function updateInfo(Request $request)
    {
        $user = Auth::user();

        $validated = $request->validate([
            'username' => [
                'sometimes',
                'required',
                'string',
                'min:3',
                'max:20',
                Rule::unique('users', 'username')->ignore($user->id),
            ],
            'email' => [
                'sometimes',
                'required',
                'email',
                'max:255',
                Rule::unique('users', 'email')->ignore($user->id),
            ],
            'study_group' => 'sometimes|nullable|string|max:100',
            'bio'         => 'sometimes|nullable|string|max:300',
        ], [
            'username.required' => 'Введите имя пользователя.',
            'username.min'      => 'Имя должно содержать минимум 3 символа.',
            'username.max'      => 'Имя не должно превышать 20 символов.',
            'username.unique'   => 'Это имя пользователя уже занято.',
            'email.required'    => 'Введите email.',
            'email.email'       => 'Введите корректный email.',
            'email.unique'      => 'Этот email уже используется.',
            'study_group.max'   => 'Максимум 100 символов.',
            'bio.max'           => 'Максимум 300 символов.',
        ]);

        $user->update($validated);

        return response()->json([
            'success' => true,
            'user'    => [
                'username'    => $user->username,
                'email'       => $user->email,
                'study_group' => $user->study_group,
                'bio'         => $user->bio,
            ],
            'message' => 'Профиль обновлён',
        ]);
    }

    /**
     * Смена пароля пользователя
     */
    public function updatePassword(Request $request)
    {
        $request->validate([
            'current_password' => 'required|string',
            'password'         => 'required|string|min:6|confirmed',
        ], [
            'current_password.required' => 'Введите текущий пароль.',
            'password.required'         => 'Введите новый пароль.',
            'password.min'              => 'Новый пароль должен содержать минимум 6 символов.',
            'password.confirmed'        => 'Пароли не совпадают.',
        ]);

        $user = Auth::user();

        if (!Hash::check($request->input('current_password'), $user->password)) {
            return response()->json([
                'errors' => [
                    'current_password' => ['Неверный текущий пароль.'],
                ],
            ], 422);
        }

        $user->update([
            'password' => Hash::make($request->input('password')),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Пароль успешно изменён',
        ]);
    }
}
