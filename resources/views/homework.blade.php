<!DOCTYPE html>
<html lang="ru">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>StudentPlanner — Домашнее задание</title>
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
            <a href="/profile">
                <img src="{{ asset('media/Profile.svg') }}" alt="Профиль">
            </a>
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

                <!-- Список домашних заданий (интерактивный через JS) -->
                <div class="my-homework" id="homeworkList">
                    <!-- Задание 1 -->
                    <div class="one-homework" data-id="1">
                        <div class="homework-info">
                            <div class="homework-name">Математика</div>
                            <div class="homework-text">Страница 83 учить правило, распечатать правило</div>
                        </div>
                        <div class="homework-meta">
                            <span class="homework-deadline">до 15.09.25</span>
                            <div class="homework-buttons">
                                <button type="button" class="hw-btn hw-btn-download" title="Скачать файл">
                                    <img src="{{ asset('media/download.svg') }}" alt="download">
                                </button>
                                <button type="button" class="hw-btn hw-btn-delete" title="Удалить задание">
                                    <img src="{{ asset('media/delete.svg') }}" alt="delete">
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Задание 2 -->
                    <div class="one-homework" data-id="2">
                        <div class="homework-info">
                            <div class="homework-name">Информатика</div>
                            <div class="homework-text">Лабораторная работа №3: спроектировать реляционную базу данных</div>
                        </div>
                        <div class="homework-meta">
                            <span class="homework-deadline">до 18.09.25</span>
                            <div class="homework-buttons">
                                <button type="button" class="hw-btn hw-btn-download" title="Скачать файл">
                                    <img src="{{ asset('media/download.svg') }}" alt="download">
                                </button>
                                <button type="button" class="hw-btn hw-btn-delete" title="Удалить задание">
                                    <img src="{{ asset('media/delete.svg') }}" alt="delete">
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Задание 3 -->
                    <div class="one-homework" data-id="3">
                        <div class="homework-info">
                            <div class="homework-name">Физика</div>
                            <div class="homework-text">Задачи 4.12 — 4.18 в сборнике задач</div>
                        </div>
                        <div class="homework-meta">
                            <span class="homework-deadline">до 20.09.25</span>
                            <div class="homework-buttons">
                                <button type="button" class="hw-btn hw-btn-download" title="Скачать файл">
                                    <img src="{{ asset('media/download.svg') }}" alt="download">
                                </button>
                                <button type="button" class="hw-btn hw-btn-delete" title="Удалить задание">
                                    <img src="{{ asset('media/delete.svg') }}" alt="delete">
                                </button>
                            </div>
                        </div>
                    </div>
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
