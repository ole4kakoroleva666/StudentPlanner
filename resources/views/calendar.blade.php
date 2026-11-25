<!DOCTYPE html>
<html lang="ru">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Календарь – SP</title>
    <link rel="icon" type="image/svg+xml" href="{{ asset('media/favicon.svg') }}">
    @vite(['resources/css/calendar.css', 'resources/js/calendar.js'])
</head>

<body>
    <header>
        <nav>
            <a href="/">
                <img class="logotip" src="{{ asset('media/newlogo.svg') }}" alt="Логотип">
            </a>
            <a href="/">Главная</a>
            <a href="/task">Задачи</a>
            <a href="/calendar" class="active">Календарь</a>
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
                    <a href="/" id="backBtn" title="На главную">
                        <img src="{{ asset('media/back.svg') }}" alt="Назад">
                    </a>
                    <p>Календарь</p>
                </div>

                <!-- Шапка календаря со стрелками и выбором месяца/года -->
                <div class="month-controls">
                    <div class="calendar-page-nav">
                        <button type="button" class="calendar-page-btn" id="calPrevBtn" aria-label="Предыдущий месяц">‹</button>
                        
                        <div class="calendar-page-selects">
                            <select class="calendar-page-select" id="calMonthSelect" aria-label="Выбор месяца"></select>
                            <select class="calendar-page-select" id="calYearSelect" aria-label="Выбор года"></select>
                        </div>

                        <button type="button" class="calendar-page-btn" id="calNextBtn" aria-label="Следующий месяц">›</button>
                    </div>
                </div>

                <div class="calendar-wrapper">
                    <div class="calendar-header">
                        <div class="week-day">Понедельник</div>
                        <div class="week-day">Вторник</div>
                        <div class="week-day">Среда</div>
                        <div class="week-day">Четверг</div>
                        <div class="week-day">Пятница</div>
                        <div class="week-day">Суббота</div>
                        <div class="week-day">Воскресенье</div>
                    </div>

                    <!-- Сетка заполняется динамически через JS -->
                    <div class="calendar-grid" id="calendarGrid"></div>
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
