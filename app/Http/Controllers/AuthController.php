<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

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
}