<!DOCTYPE html>
<html lang="ru">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Вход - StudentPlanner</title>

    <!-- Подключение стилей через Vite -->
    @vite(['resources/css/login.css'])
</head>

<body>
    <main>
        <div class="container-login">
            <div class="login">
                <h1>Войти</h1>

                <!-- Форма с методом POST и CSRF-токеном -->
                <form method="POST" action="/login">
                    @csrf
                    
                    <!-- Поле email/username -->
                    <div class="login-input">
                        <input type="text" name="login" placeholder="Адрес электронной почты/Имя пользователя" value="{{ old('login') }}" required>
                        <!-- Отображение ошибки валидации -->
                        @error('login')
                            <span class="error-message">{{ $message }}</span>
                        @enderror
                    </div>

                    <!-- Поле пароль -->
                    <div class="login-input last-input">
                        <input type="password" name="password" placeholder="Пароль" required>
                        <!-- Отображение ошибки валидации -->
                        @error('password')
                            <span class="error-message">{{ $message }}</span>
                        @enderror
                    </div>

                    <!-- Ссылка "Забыли пароль?" -->
                    <div class="login-forgot">
                        <a href="/forgot-password">Забыли пароль?</a>
                    </div>

                    <!-- Кнопки действий -->
                    <div class="login-actions">
                        <button type="submit" class="login-btn">Войти</button>
                        <div class="register-link">
                            У вас еще нет аккаунта? <a href="/register">Зарегистрироваться</a>
                        </div>
                    </div>
                </form>

                <!-- Отображение общих ошибок -->
                @if(session('error'))
                    <div class="alert-error">
                        {{ session('error') }}
                    </div>
                @endif
            </div>
        </div>
    </main>
</body>

</html>