<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class AuthController extends Controller
{
    /**
     * Показ страницы регистрации
     */
    public function showRegister()
    {
        return view('register');
    }

    /**
     * Обработка отправки формы регистрации
     */
    public function register(Request $request)
    {
        // 1. Валидация входных данных
        $validated = $request->validate([
            'email' => 'required|email|max:255|unique:users,email',
            'username' => 'required|string|min:3|max:20|unique:users,username',
            'password' => 'required|string|min:6|confirmed',
        ], [
            'email.required' => 'Введите email',
            'email.email' => 'Введите корректный email',
            'email.unique' => 'Этот email уже зарегистрирован',
            'username.required' => 'Введите имя пользователя',
            'username.min' => 'Имя должно содержать минимум 3 символа',
            'username.max' => 'Имя не должно превышать 20 символов',
            'username.unique' => 'Это имя уже занято',
            'password.required' => 'Введите пароль',
            'password.min' => 'Пароль должен содержать минимум 6 символов',
            'password.confirmed' => 'Пароли не совпадают',
        ]);

        // 2. Создание пользователя в базе данных
        $user = User::create([
            'email' => $validated['email'],
            'username' => $validated['username'],
            'password' => Hash::make($validated['password']),
        ]);

        // 3. Автоматический вход в систему под созданным аккаунтом
        Auth::login($user);

        // 4. Перенаправление на главную страницу с сообщением об успехе
        return redirect('/')->with('success', 'Регистрация прошла успешно!');
    }

    /**
     * Показ страницы входа
     */
    public function showLogin()
    {
        return view('login');
    }

    /**
     * Обработка отправки формы входа
     */
    public function login(Request $request)
    {
        // 1. Валидация полей
        $credentials = $request->validate([
            'login' => 'required|string',
            'password' => 'required|string',
        ], [
            'login.required' => 'Введите email или имя пользователя',
            'password.required' => 'Введите пароль',
        ]);

        // 2. Определяем, что ввёл пользователь: email или username
        $fieldType = filter_var($credentials['login'], FILTER_VALIDATE_EMAIL) ? 'email' : 'username';

        // 3. Попытка аутентификации
        if (Auth::attempt([$fieldType => $credentials['login'], 'password' => $credentials['password']])) {
            $request->session()->regenerate(); // Защита от фиксации сессии (безопасность)
            return redirect()->intended('/');   // Перенаправляем на главную
        }

        // 4. Если пароль или логин не подошли — возвращаем назад с ошибкой
        return back()->withErrors([
            'login' => 'Неверное имя пользователя/email или пароль.',
        ])->onlyInput('login');
    }

    /**
     * Выход из аккаунта
     */
    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/login');
    }

    /**
     * Запрос на восстановление пароля (поп-ап на странице входа)
     */
    public function sendResetLink(Request $request)
    {
        $validated = $request->validate([
            'email' => 'required|email|exists:users,email',
        ], [
            'email.required' => 'Введите email, указанный при регистрации.',
            'email.email'    => 'Введите корректный email.',
            'email.exists'   => 'Пользователь с таким email не найден.',
        ]);

        $token = Str::random(64);

        DB::table('password_reset_tokens')->updateOrInsert(
            ['email' => $validated['email']],
            [
                'token'      => Hash::make($token),
                'created_at' => now(),
            ]
        );

        $resetUrl = route('password.reset', ['token' => $token, 'email' => $validated['email']]);

        $mailSent = false;
        try {
            if (config('mail.default') !== 'log') {
                Mail::raw("Ссылка для сброса пароля в StudentPlanner:\n\n{$resetUrl}", function ($message) use ($validated) {
                    $message->to($validated['email'])->subject('Сброс пароля – StudentPlanner');
                });
                $mailSent = true;
            }
        } catch (\Throwable $e) {
            $mailSent = false;
        }

        return response()->json([
            'success'   => true,
            'message'   => $mailSent
                ? 'Письмо со ссылкой для сброса пароля отправлено на ваш email.'
                : 'Ссылка для сброса пароля создана.',
            'reset_url' => (!$mailSent || app()->environment('local')) ? $resetUrl : null,
        ]);
    }

    /**
     * Страница ввода нового пароля по токену
     */
    public function showResetPassword(Request $request, string $token)
    {
        return view('reset-password', [
            'token' => $token,
            'email' => $request->query('email', ''),
        ]);
    }

    /**
     * Сохранение нового пароля
     */
    public function resetPassword(Request $request)
    {
        $validated = $request->validate([
            'token'    => 'required|string',
            'email'    => 'required|email|exists:users,email',
            'password' => 'required|string|min:6|confirmed',
        ], [
            'email.required'     => 'Введите email.',
            'email.exists'       => 'Пользователь не найден.',
            'password.required'  => 'Введите новый пароль.',
            'password.min'       => 'Пароль должен содержать минимум 6 символов.',
            'password.confirmed' => 'Пароли не совпадают.',
        ]);

        $record = DB::table('password_reset_tokens')
            ->where('email', $validated['email'])
            ->first();

        if (!$record || !Hash::check($validated['token'], $record->token)) {
            return back()->withErrors([
                'email' => 'Ссылка для сброса пароля недействительна или устарела.',
            ])->withInput();
        }

        $user = User::where('email', $validated['email'])->first();
        $user->update(['password' => Hash::make($validated['password'])]);

        DB::table('password_reset_tokens')->where('email', $validated['email'])->delete();

        return redirect()->route('login')->with('status', 'Пароль успешно изменён! Теперь вы можете войти.');
    }
}