<!DOCTYPE html>
<html lang="ru">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>StudentPlanner — Организуй свою учебу легко</title>
    
    <!-- Подключение стилей и скриптов через Vite -->
    @vite(['resources/css/welcome.css', 'resources/js/welcome.js'])
</head>

<body>
    <main class="welcome-main">
        <div class="welcome-container">
            
            <!-- Левая колонка: Логотип, слоган и кнопки -->
            <div class="welcome-left">
                <div class="logotip">
                    <img src="{{ asset('media/lalalalal 1.svg') }}" alt="StudentPlanner">
                </div>
                
                <div class="main-text">
                    <h1>Организуй свою учебу легко</h1>
                    <p>Онлайн-органайзер для учебы, в котором ты сможешь хранить всё самое важное в одном месте.</p>
                </div>

                <div class="login-actions">
                    @auth
                        <!-- Если пользователь уже авторизован -->
                        <a href="/"><button class="btn-login" style="width: auto; padding: 0 25px;">Перейти в планер ➔</button></a>
                    @else
                        <!-- Если гость -->
                        <a href="/login"><button class="btn-login">Войти</button></a>
                        <a href="/register"><button class="btn-register">Регистрация</button></a>
                    @endauth
                </div>
            </div>

            <!-- Правая колонка: Карточки возможностей -->
            <div class="welcome-right">
                
                <div class="cards-title">
                    <h2>Возможности планера</h2>
                </div>

                <div class="cards">
                    
                    <!-- 1. Календарь -->
                    <div class="card">
                        <div class="card-icon">
                            <img src="{{ asset('media/calendar.svg') }}" alt="Календарь">
                        </div>
                        <div class="card-text">
                            <h3>Календарь</h3>
                            <p>Планируй учебный год и отслеживай важные даты</p>
                        </div>
                    </div>

                    <!-- 2. Заметки -->
                    <div class="card">
                        <div class="card-icon">
                            <img src="{{ asset('media/ex.svg') }}" alt="Заметки">
                        </div>
                        <div class="card-text">
                            <h3>Заметки</h3>
                            <p>Храни конспекты лекций и закрепляй важное</p>
                        </div>
                    </div>

                    <!-- 3. Задачи -->
                    <div class="card">
                        <div class="card-icon">
                            <img src="{{ asset('media/list.svg') }}" alt="Задачи">
                        </div>
                        <div class="card-text">
                            <h3>Задачи</h3>
                            <p>Управляй дедлайнами и списками дел</p>
                        </div>
                    </div>

                    <!-- 4. Расписание -->
                    <div class="card">
                        <div class="card-icon">
                            <img src="{{ asset('media/calendar (2).svg') }}" alt="Расписание">
                        </div>
                        <div class="card-text">
                            <h3>Расписание</h3>
                            <p>Смотри расписание пар и аудиторий</p>
                        </div>
                    </div>

                    <!-- 5. Файлы -->
                    <div class="card">
                        <div class="card-icon">
                            <img src="{{ asset('media/fiels.svg') }}" alt="Файлы">
                        </div>
                        <div class="card-text">
                            <h3>Файлы</h3>
                            <p>Загружай и храни курсовые и методички</p>
                        </div>
                    </div>

                    <!-- 6. Напоминания -->
                    <div class="card">
                        <div class="card-icon">
                            <img src="{{ asset('media/reminder.svg') }}" alt="Напоминания">
                        </div>
                        <div class="card-text">
                            <h3>Напоминания</h3>
                            <p>Контролируй приближение сроков сдачи</p>
                        </div>
                    </div>

                </div>
            </div>

        </div>
    </main>
</body>

</html>