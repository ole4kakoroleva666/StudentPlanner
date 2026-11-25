<!DOCTYPE html>
<html lang="ru">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Расписание – SP</title>
    <link rel="icon" type="image/svg+xml" href="{{ asset('media/favicon.svg') }}">
    @vite(['resources/css/shedule.css'])
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
            <a href="/shedule" class="active">Расписание</a>
            <a href="/notes">Заметки</a>
            <a href="/privateFiels">Моё</a>
            @include('partials.header-avatar')
        </nav>
    </header>

    <main>
        <section>
            <div class="container">
                <div class="title">
                    <a href="/">
                        <img src="{{ asset('media/back.svg') }}" alt="back">
                    </a>
                    <p>Расписание</p>
                </div>

                <!-- Блок даты недели со стрелочками слева и справа -->
                <div class="week-date-container">
                    <button type="button" class="arrow-btn prev-btn" id="prevWeekBtn" title="Предыдущая неделя" aria-label="Предыдущая неделя">‹</button>
                    <p class="week-date-range" id="weekDateRange">с 16.11 по 22.11</p>
                    <button type="button" class="arrow-btn next-btn" id="nextWeekBtn" title="Следующая неделя" aria-label="Следующая неделя">›</button>
                </div>

                <!-- Ровно ОДНА неделя (6 карточек: 3 на первой строчке, 3 на второй) -->
                <div class="schedule-container">
                    <div class="all-card" id="scheduleCards">
                        <!-- Заполняется динамически через JS -->
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

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const ICONS = {
                time: "{{ asset('media/time.svg') }}",
                books: "{{ asset('media/books.svg') }}",
                classroom: "{{ asset('media/chool.svg') }}",
                teacher: "{{ asset('media/teacher.svg') }}"
            };

            const weeks = [
                {
                    dateRange: 'с 16.11 по 22.11',
                    days: [
                        {
                            day: 'Понедельник',
                            date: '16 ноября',
                            lessons: [
                                { time: '14:15 – 15:50', subject: 'ПИРВП', classroom: '1-306', teacher: 'Евдокимов' },
                                { time: '16:10 – 17:45', subject: 'ПИРВП', classroom: '1-306', teacher: 'Евдокимов' },
                                { time: '17:55 – 19:30', subject: 'Стандартизация', classroom: '2-204', teacher: 'Сухорукова' }
                            ]
                        },
                        {
                            day: 'Вторник',
                            date: '17 ноября',
                            lessons: [
                                { time: '10:15 – 11:50', subject: 'ОВБП', classroom: '3-102', teacher: 'Бушуева' },
                                { time: '12:30 – 14:05', subject: 'ОВБП', classroom: '3-102', teacher: 'Бушуева' },
                                { time: '14:15 – 15:50', subject: 'Стандартизация', classroom: '2-204', teacher: 'Сухорукова' }
                            ]
                        },
                        {
                            day: 'Среда',
                            date: '18 ноября',
                            lessons: [
                                { time: '10:15 – 11:50', subject: 'Стандартизация', classroom: '2-204', teacher: 'Сухорукова' },
                                { time: '12:30 – 14:05', subject: 'ПИРВП', classroom: '1-306', teacher: 'Евдокимов' },
                                { time: '14:15 – 15:50', subject: 'ОВБП', classroom: '3-102', teacher: 'Бушуева' }
                            ]
                        },
                        {
                            day: 'Четверг',
                            date: '19 ноября',
                            lessons: [
                                { time: '14:15 – 15:50', subject: 'ОВБП', classroom: '3-102', teacher: 'Бушуева' },
                                { time: '16:10 – 17:45', subject: 'Стандартизация', classroom: '2-204', teacher: 'Сухорукова' },
                                { time: '17:55 – 19:30', subject: 'ПИРВП', classroom: '1-306', teacher: 'Евдокимов' }
                            ]
                        },
                        {
                            day: 'Пятница',
                            date: '20 ноября',
                            lessons: [
                                { time: '08:30 – 10:05', subject: 'Стандартизация', classroom: '2-204', teacher: 'Сухорукова' },
                                { time: '10:15 – 11:50', subject: 'ПИРВП', classroom: '1-306', teacher: 'Евдокимов' },
                                { time: '12:30 – 14:05', subject: 'ОВБП', classroom: '3-102', teacher: 'Бушуева' }
                            ]
                        },
                        {
                            day: 'Суббота',
                            date: '21 ноября',
                            lessons: [
                                { time: '10:15 – 11:50', subject: 'ПИРВП', classroom: '1-306', teacher: 'Евдокимов' },
                                { time: '12:30 – 14:05', subject: 'Стандартизация', classroom: '2-204', teacher: 'Сухорукова' }
                            ]
                        }
                    ]
                },
                {
                    dateRange: 'с 23.11 по 29.11',
                    days: [
                        {
                            day: 'Понедельник',
                            date: '23 ноября',
                            lessons: [
                                { time: '10:15 – 11:50', subject: 'ОВБП', classroom: '3-102', teacher: 'Бушуева' },
                                { time: '12:30 – 14:05', subject: 'ПИРВП', classroom: '1-306', teacher: 'Евдокимов' }
                            ]
                        },
                        {
                            day: 'Вторник',
                            date: '24 ноября',
                            lessons: [
                                { time: '12:30 – 14:05', subject: 'Стандартизация', classroom: '2-204', teacher: 'Сухорукова' },
                                { time: '14:15 – 15:50', subject: 'ПИРВП', classroom: '1-306', teacher: 'Евдокимов' },
                                { time: '16:10 – 17:45', subject: 'ОВБП', classroom: '3-102', teacher: 'Бушуева' }
                            ]
                        },
                        {
                            day: 'Среда',
                            date: '25 ноября',
                            lessons: [
                                { time: '08:30 – 10:05', subject: 'ПИРВП', classroom: '1-306', teacher: 'Евдокимов' },
                                { time: '10:15 – 11:50', subject: 'ОВБП', classroom: '3-102', teacher: 'Бушуева' }
                            ]
                        },
                        {
                            day: 'Четверг',
                            date: '26 ноября',
                            lessons: [
                                { time: '12:30 – 14:05', subject: 'Стандартизация', classroom: '2-204', teacher: 'Сухорукова' },
                                { time: '14:15 – 15:50', subject: 'Стандартизация', classroom: '2-204', teacher: 'Сухорукова' },
                                { time: '16:10 – 17:45', subject: 'ПИРВП', classroom: '1-306', teacher: 'Евдокимов' }
                            ]
                        },
                        {
                            day: 'Пятница',
                            date: '27 ноября',
                            lessons: [
                                { time: '10:15 – 11:50', subject: 'ПИРВП', classroom: '1-306', teacher: 'Евдокимов' },
                                { time: '12:30 – 14:05', subject: 'ОВБП', classroom: '3-102', teacher: 'Бушуева' }
                            ]
                        },
                        {
                            day: 'Суббота',
                            date: '28 ноября',
                            lessons: []
                        }
                    ]
                },
                {
                    dateRange: 'с 30.11 по 06.12',
                    days: [
                        {
                            day: 'Понедельник',
                            date: '30 ноября',
                            lessons: [
                                { time: '14:15 – 15:50', subject: 'ПИРВП', classroom: '1-306', teacher: 'Евдокимов' },
                                { time: '16:10 – 17:45', subject: 'ОВБП', classroom: '3-102', teacher: 'Бушуева' },
                                { time: '17:55 – 19:30', subject: 'Стандартизация', classroom: '2-204', teacher: 'Сухорукова' }
                            ]
                        },
                        {
                            day: 'Вторник',
                            date: '1 декабря',
                            lessons: [
                                { time: '08:30 – 10:05', subject: 'ОВБП', classroom: '3-102', teacher: 'Бушуева' },
                                { time: '10:15 – 11:50', subject: 'Стандартизация', classroom: '2-204', teacher: 'Сухорукова' }
                            ]
                        },
                        {
                            day: 'Среда',
                            date: '2 декабря',
                            lessons: [
                                { time: '10:15 – 11:50', subject: 'ПИРВП', classroom: '1-306', teacher: 'Евдокимов' },
                                { time: '12:30 – 14:05', subject: 'Стандартизация', classroom: '2-204', teacher: 'Сухорукова' },
                                { time: '14:15 – 15:50', subject: 'ОВБП', classroom: '3-102', teacher: 'Бушуева' }
                            ]
                        },
                        {
                            day: 'Четверг',
                            date: '3 декабря',
                            lessons: [
                                { time: '12:30 – 14:05', subject: 'ПИРВП', classroom: '1-306', teacher: 'Евдокимов' },
                                { time: '14:15 – 15:50', subject: 'ПИРВП', classroom: '1-306', teacher: 'Евдокимов' }
                            ]
                        },
                        {
                            day: 'Пятница',
                            date: '4 декабря',
                            lessons: [
                                { time: '10:15 – 11:50', subject: 'ОВБП', classroom: '3-102', teacher: 'Бушуева' },
                                { time: '12:30 – 14:05', subject: 'ОВБП', classroom: '3-102', teacher: 'Бушуева' },
                                { time: '14:15 – 15:50', subject: 'Стандартизация', classroom: '2-204', teacher: 'Сухорукова' }
                            ]
                        },
                        {
                            day: 'Суббота',
                            date: '5 декабря',
                            lessons: [
                                { time: '08:30 – 10:05', subject: 'ПИРВП', classroom: '1-306', teacher: 'Евдокимов' },
                                { time: '10:15 – 11:50', subject: 'ОВБП', classroom: '3-102', teacher: 'Бушуева' }
                            ]
                        }
                    ]
                }
            ];

            let currentWeekIndex = 0;
            const weekDateRangeEl = document.getElementById('weekDateRange');
            const scheduleCardsEl = document.getElementById('scheduleCards');
            const prevWeekBtn = document.getElementById('prevWeekBtn');
            const nextWeekBtn = document.getElementById('nextWeekBtn');

            function renderWeek(index) {
                const data = weeks[index];
                if (!data) return;

                weekDateRangeEl.textContent = data.dateRange;

                let html = '';
                data.days.forEach(day => {
                    let lessonsHtml = '';
                    if (day.lessons && day.lessons.length > 0) {
                        day.lessons.forEach(l => {
                            lessonsHtml += `
                                <div class="lesson">
                                    <div class="time">
                                        <img class="icons" src="${ICONS.time}" alt="time" />
                                        <p>Время: ${l.time}</p>
                                    </div>
                                    <div class="subject">
                                        <img class="icons" src="${ICONS.books}" alt="subject" />
                                        <p>Предмет: ${l.subject}</p>
                                    </div>
                                    <div class="classroom">
                                        <img class="icons" src="${ICONS.classroom}" alt="classroom" />
                                        <p>Аудитория: ${l.classroom}</p>
                                    </div>
                                    <div class="teacher">
                                        <img class="icons" src="${ICONS.teacher}" alt="teacher" />
                                        <p>Преподаватель: ${l.teacher}</p>
                                    </div>
                                </div>
                            `;
                        });
                    } else {
                        lessonsHtml = `
                            <div class="no-lessons">
                                <p>Пар нет</p>
                            </div>
                        `;
                    }

                    html += `
                        <div class="day-card">
                            <div class="day-header">
                                <h3>${day.day}</h3>
                                <p class="date">${day.date}</p>
                            </div>
                            <div class="day-lessons">
                                ${lessonsHtml}
                            </div>
                        </div>
                    `;
                });

                scheduleCardsEl.innerHTML = html;
            }

            renderWeek(currentWeekIndex);

            prevWeekBtn.addEventListener('click', function () {
                currentWeekIndex = (currentWeekIndex - 1 + weeks.length) % weeks.length;
                renderWeek(currentWeekIndex);
            });

            nextWeekBtn.addEventListener('click', function () {
                currentWeekIndex = (currentWeekIndex + 1) % weeks.length;
                renderWeek(currentWeekIndex);
            });
        });
    </script>
</body>

</html>
</body>

</html>
</body>

</html>
