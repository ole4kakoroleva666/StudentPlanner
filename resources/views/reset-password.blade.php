<!DOCTYPE html>
<html lang="ru">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Сброс пароля – SP</title>
    <link rel="icon" type="image/svg+xml" href="{{ asset('media/favicon.svg') }}">
    @vite(['resources/css/login.css'])
</head>

<body>
    <main>
        <div class="container-login">
            <div class="login">
                <h1>Новый пароль</h1>

                <form method="POST" action="{{ route('password.update') }}">
                    @csrf
                    <input type="hidden" name="token" value="{{ $token }}">

                    <div class="login-input">
                        <input type="email" name="email" placeholder="Ваш email" value="{{ old('email', $email) }}" required>
                        @error('email')
                            <span class="error-message">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="login-input">
                        <input type="password" name="password" placeholder="Новый пароль (минимум 6 символов)" required>
                        @error('password')
                            <span class="error-message">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="login-input last-input">
                        <input type="password" name="password_confirmation" placeholder="Повторите новый пароль" required>
                    </div>

                    <div class="login-actions" style="margin-top: 2.5rem;">
                        <button type="submit" class="login-btn">Сохранить пароль</button>
                        <div class="register-link">
                            <a href="/login">Вернуться ко входу</a>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </main>
</body>

</html>
