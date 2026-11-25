<!DOCTYPE html>
<html lang="ru">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Домашка – SP</title>
    <link rel="icon" type="image/svg+xml" href="{{ asset('media/favicon.svg') }}">
    @vite(['resources/css/homework.css', 'resources/js/homework.js'])
</head>

<body>
    <header>
        <nav>
            <a href="/">
                <img class="logotip" src="{{ asset('media/newlogo.svg') }}" alt="Логотип">
            </a>
            <a href="/">Главная</a>
            <a href="/task">Задачи</a>
            <a href="/calendar">Календарь</a>
            <a href="/homework" class="active">Домашнее задание</a>
            <a href="/shedule">Расписание</a>
            <a href="/notes">Заметки</a>
            <a href="/privateFiels">Моё</a>
            @include('partials.header-avatar')
        </nav>
    </header>

    <main>
        <section>
            <div class="container">
                <div class="title">
                    <a href="/" id="backBtn" title="На главную">
                        <img src="{{ asset('media/back.svg') }}" alt="Назад">
                    </a>
                    <p>Домашнее задание</p>
                </div>

                <div class="create-my-homework">
                    <a href="/homeworkNew" class="add-hw-btn">
                        <img src="{{ asset('media/plus.svg') }}" alt="plus">
                        Добавить дз
                    </a>
                </div>

                <p class="subtitle">Ближайшее домашнее задание</p>

                <!-- Панель управления: сортировка и поиск -->
                <div class="hw-controls">
                    <div class="hw-sort-group">
                        <span class="hw-controls-label">Сортировка:</span>
                        <div class="hw-sort-toggle">
                            <button type="button" class="hw-sort-btn active" data-sort="deadline">По дедлайну</button>
                            <button type="button" class="hw-sort-btn" data-sort="added">По добавлению</button>
                        </div>
                    </div>
                    <div class="hw-filter-group">
                        <input type="text" id="hwSearchInput" class="hw-search" placeholder="Поиск по предмету или заданию...">
                    </div>
                </div>

                <!-- Список домашних заданий (данные из БД) -->
                <div class="my-homework"
                     id="homeworkList"
                     data-homeworks='@json($homeworks ?? [])'>
                </div>

            </div>
        </section>

        <footer>
            <div class="footer-container">
                <div class="footer-left">
                    <img class="footer-logo" src="{{ asset('media/newlogo.svg') }}" alt="Логотип">
                    <p class="footer-desc">Твой личный помощник в обучении</p>
                </div>

                <div class="footer-info">
                    <h4>О нас</h4>
                    <p>“StudentPlanner” — это онлайн-органайзер для учебы, в котором ты сможешь хранить всё самое важное в одном месте</p>
                </div>

                <div class="footer-contact">
                    <h4>Контакты</h4>
                    <p>Email: support@studentplanner.app</p>
                    <p>Версия: 1.0.0</p>

                    <div class="footer-social">
                        <h4>Мы в соцсетях</h4>
                        <div class="social-icons">
                            <img src="{{ asset('media/goglenew.svg') }}" alt="google">
                            <img src="{{ asset('media/telegram.svg') }}" alt="Telegram">
                            <img src="{{ asset('media/vk.svg') }}" alt="VK">
                        </div>
                    </div>
                </div>
            </div>

            <div class="footer-bottom">
                <p>© 2025 StudentPlanner. Все права защищены.</p>
            </div>
        </footer>
    </main>
</body>

</html>
