<!DOCTYPE html>
<html lang="ru">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Вход – SP</title>
    <link rel="icon" type="image/svg+xml" href="{{ asset('media/favicon.svg') }}">

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
                        <a href="/forgot-password" id="openForgotModal">Забыли пароль?</a>
                    </div>

                    <!-- Кнопки действий -->
                    <div class="login-actions">
                        <button type="submit" class="login-btn">Войти</button>
                        <div class="register-link">
                            У вас еще нет аккаунта? <a href="/register">Зарегистрироваться</a>
                        </div>
                    </div>
                </form>

                @if(session('status'))
                    <div class="alert-success">
                        {{ session('status') }}
                    </div>
                @endif

                <!-- Отображение общих ошибок -->
                @if(session('error'))
                    <div class="alert-error">
                        {{ session('error') }}
                    </div>
                @endif
            </div>
        </div>

        <!-- Поп-ап восстановления пароля -->
        <div class="forgot-backdrop" id="forgotModalBackdrop" hidden>
            <div class="forgot-modal" role="dialog" aria-modal="true" aria-labelledby="forgotModalTitle">
                <div class="forgot-modal-head">
                    <h2 id="forgotModalTitle">Восстановление пароля</h2>
                    <button type="button" class="forgot-modal-close" id="forgotModalClose" aria-label="Закрыть">×</button>
                </div>
                <form id="forgotPasswordForm" class="forgot-modal-body">
                    <p class="forgot-desc">Укажите email, привязанный к вашему аккаунту, и мы подготовим ссылку для смены пароля.</p>
                    <input type="email" id="forgotEmailInput" class="forgot-input" placeholder="Ваш email" required>
                    <p class="forgot-error" id="forgotError"></p>
                    <div class="forgot-success" id="forgotSuccess" hidden></div>
                    <div class="forgot-actions">
                        <button type="button" class="forgot-btn-cancel" id="forgotCancelBtn">Отмена</button>
                        <button type="submit" class="forgot-btn-submit" id="forgotSubmitBtn">Сбросить пароль</button>
                    </div>
                </form>
            </div>
        </div>
    </main>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const openBtn = document.getElementById('openForgotModal');
            const backdrop = document.getElementById('forgotModalBackdrop');
            const closeBtn = document.getElementById('forgotModalClose');
            const cancelBtn = document.getElementById('forgotCancelBtn');
            const form = document.getElementById('forgotPasswordForm');
            const emailInput = document.getElementById('forgotEmailInput');
            const errorEl = document.getElementById('forgotError');
            const successEl = document.getElementById('forgotSuccess');
            const submitBtn = document.getElementById('forgotSubmitBtn');
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';

            function openModal(e) {
                e.preventDefault();
                errorEl.textContent = '';
                successEl.hidden = true;
                successEl.innerHTML = '';
                backdrop.hidden = false;
                emailInput.focus();
            }

            function closeModal() {
                backdrop.hidden = true;
            }

            openBtn?.addEventListener('click', openModal);
            closeBtn?.addEventListener('click', closeModal);
            cancelBtn?.addEventListener('click', closeModal);
            backdrop?.addEventListener('click', (e) => {
                if (e.target === backdrop) closeModal();
            });

            form?.addEventListener('submit', async (e) => {
                e.preventDefault();
                errorEl.textContent = '';
                successEl.hidden = true;
                successEl.innerHTML = '';
                submitBtn.disabled = true;

                try {
                    const res = await fetch('/forgot-password', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': csrfToken,
                            'Accept': 'application/json',
                        },
                        body: JSON.stringify({ email: emailInput.value.trim() }),
                    });
                    const data = await res.json();

                    if (!res.ok) {
                        errorEl.textContent = data?.errors?.email?.[0] || data?.message || 'Ошибка запроса';
                        return;
                    }

                    successEl.hidden = false;
                    const msgP = document.createElement('p');
                    msgP.textContent = data.message || 'Готово!';
                    successEl.appendChild(msgP);

                    if (data.reset_url) {
                        const link = document.createElement('a');
                        link.href = data.reset_url;
                        link.className = 'forgot-reset-link';
                        link.textContent = 'Перейти к смене пароля →';
                        successEl.appendChild(link);
                    }
                } catch {
                    errorEl.textContent = 'Ошибка соединения с сервером';
                } finally {
                    submitBtn.disabled = false;
                }
            });
        });
    </script>
</body>

</html>