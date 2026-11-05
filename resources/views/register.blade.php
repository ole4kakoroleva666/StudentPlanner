
<!DOCTYPE html>
<html lang="ru">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Регистрация - StudentPlanner</title>

    <!-- Подключение стилей через Vite -->
    @vite(['resources/css/register.css'])
</head>

<body>
    <main>
        <div class="container-register">
            <div class="register">
                <h1>Регистрация</h1>

                <!-- Форма с методом POST и CSRF-токеном -->
                <form method="POST" action="/register">
                    @csrf

                    <!-- Поле Email -->
                    <div class="register-input">
                        <input type="email" name="email" placeholder="Адрес электронной почты" 
                               value="{{ old('email') }}" required>
                        @error('email')
                            <span class="error-message">{{ $message }}</span>
                        @enderror
                    </div>

                    <!-- Поле Имя пользователя -->
                    <div class="register-input">
                        <input type="text" name="username" placeholder="Имя пользователя" 
                               value="{{ old('username') }}" required minlength="3">
                        @error('username')
                            <span class="error-message">{{ $message }}</span>
                        @enderror
                    </div>

                    <!-- Поле Пароль -->
                    <div class="register-input">
                        <input type="password" name="password" placeholder="Пароль" required minlength="6">
                        @error('password')
                            <span class="error-message">{{ $message }}</span>
                        @enderror
                    </div>

                    <!-- Подтверждение пароля -->
                    <div class="register-input">
                        <input type="password" name="password_confirmation" placeholder="Подтвердите пароль" required>
                        @error('password_confirmation')
                            <span class="error-message">{{ $message }}</span>
                        @enderror
                    </div>

                    <!-- Кнопки действий -->
                    <div class="register-actions">
                        <button type="submit" class="register-btn">Создать</button>
                        <div class="login-link">
                            У вас уже есть аккаунт? <a href="/login">Войти</a>
                        </div>
                    </div>
                </form>

                <!-- Отображение общих ошибок -->
                @if(session('error'))
                    <div class="alert-error">
                        {{ session('error') }}
                    </div>
                @endif

                <!-- Уведомление об успешной регистрации -->
                @if(session('success'))
                    <div class="alert-success">
                        {{ session('success') }}
                    </div>
                @endif
            </div>
        </div>
    </main>
</body>

</html>