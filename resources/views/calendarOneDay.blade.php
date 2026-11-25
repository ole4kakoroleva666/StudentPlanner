<!DOCTYPE html>
<html lang="ru">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>План на день – SP</title>
    <link rel="icon" type="image/svg+xml" href="{{ asset('media/favicon.svg') }}">
    @vite(['resources/css/calendarOneDay.css', 'resources/js/calendarOneDay.js'])
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
            <a href="/homework">Домашнее задание</a>
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
                    <a href="/calendar" id="backToCalendarBtn">
                        <img src="{{ asset('media/back.svg') }}" alt="back">
                    </a>
                    <p>Расписание</p>
                </div>

                <div class="week-mont">
                    <div class="month">
                        <p id="oneDayDateText">20 сентября</p>
                    </div>
                    <div class="week">
                        <p id="oneDayWeekText">Суббота</p>
                    </div>
                </div>

                <div class="main-calendarOneDay">
                    <!-- ЛЕВАЯ КОЛОНКА: РАСПИСАНИЕ -->
                    <div class="shedule-calendarOneDay">
                        <div class="shedule-header">
                            <p>Расписание</p>
                            <img src="{{ asset('media/lessonNew.svg') }}" alt="lesson">
                        </div>

                        <!-- Контейнер для пар дня (заполняется через JS) -->
                        <div id="lessonsContainer"></div>
                    </div>

                    <!-- ПРАВАЯ КОЛОНКА: ДЗ И ЗАМЕТКИ -->
                    <div class="right-column">
                        <!-- Блок Домашнее задание (только просмотр) -->
                        <div class="homeworkblock">
                            <div class="homework-header">
                                <p>Домашнее задание</p>
                                <img src="{{ asset('media/ex.svg') }}" alt="homework">
                            </div>

                            <div class="homework-tasks" id="hwList"></div>
                        </div>

                        <!-- Блок Заметки (с удобным полем ввода) -->
                        <div class="notesblock">
                            <div class="notes-header">
                                <p>Заметки</p>
                            </div>

                            <!-- Поле ввода, как на главной странице -->
                            <div class="note-input-box">
                                <input type="text" id="dayNoteInput" placeholder="Добавить заметку...">
                            </div>

                            <div class="notes-list" id="notesList"></div>
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
                    <p>“StudentPlanner” — это онлайн-органайзер для учебы,
                        в котором ты сможешь хранить всё самое важное в одном месте</p>
                </div>

                <div class="footer-contact">
                    <h4>Контакты</h4>
                    <p>Email: support@studentplanner.app</p>
                    <p>Версия: 1.0.0</p>

                    <div class="footer-social">
                        <h4>Мы в соцсетях</h4>
                        <div class="social-icons">
                            <img src="{{ asset('media/goglenew.svg') }}" alt="google">
                            <img src="{{ asset('media/telegram.svg') }}" alt="telegram">
                            <img src="{{ asset('media/vk.svg') }}" alt="vk">
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
