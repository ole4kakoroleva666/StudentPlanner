<!DOCTYPE html>
<html lang="ru">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>StudentPlanner — Главная</title>
    @vite(['resources/css/style.css', 'resources/js/index.js'])
</head>

<body>

    <header>
        <nav>
            <a href="/">
                <img class="logotip" src="{{ asset('media/newlogo.svg') }}" alt="Логотип">
            </a>

            <!-- Ссылки на страницы Laravel -->
            <a href="/">Главная</a>
            <a href="/task">Задачи</a>
            <a href="/calendar">Календарь</a>
            <a href="/homework">Домашнее задание</a>
            <a href="/shedule">Расписание</a>
            <a href="/notes">Заметки</a>
            <a href="/profile">Моё</a>

            <a href="/profile">
                <img src="{{ asset('media/Profile.svg') }}" alt="Профиль">
            </a>
        </nav>
    </header>

    <main>
        <section>
            <div class="container">

                <div class="cards">

                    <!-- КАРТОЧКА 1: ЗАДАЧИ -->
                    <div class="card">
                        <div class="card-head">
                            <a href="/task" style="text-decoration: none; color: inherit;">
                                <h2>Задачи ➔</h2>
                            </a>
                        </div>

                        <div class="card-body">
                            <div class="card-body-input">
                                <input type="text" id="taskInput" placeholder="Написать задачу...">
                                <button id="addTaskBtn">Добавить</button>
                            </div>

                            <div class="card-body-tasks" id="tasksContainer">
                                @forelse($tasks ?? [] as $task)
                                    <div class="card-body-tasks-task" data-id="{{ $task->id }}">
                                        <input type="checkbox" id="task_{{ $task->id }}" {{ $task->is_completed ? 'checked' : '' }}>
                                        <div class="task-text-content {{ $task->deadline ? 'has-deadline' : '' }}">
                                            <label for="task_{{ $task->id }}" title="{{ $task->title }}">
                                                {{ $task->title }}
                                            </label>
                                            @if($task->deadline)
                                                <p>
                                                    до {{ \Carbon\Carbon::parse($task->deadline)->format('d.m.y') }}
                                                </p>
                                            @endif
                                        </div>
                                    </div>
                                @empty
                                    <p class="empty-state" style="color: #9AA0C3; font-size: 1.6rem; text-align: center; margin-top: 3rem;">У вас пока нет задач. Добавьте первую выше!</p>
                                @endforelse
                            </div>
                        </div>
                    </div>


                    <!-- КАРТОЧКА 2: КАЛЕНДАРЬ -->
                    <div class="card">
                        <div class="card-head">
                            <a href="/calendar" style="text-decoration: none; color: inherit;">
                                <h2>Календарь ➔</h2>
                            </a>
                        </div>

                        <div class="card-body-calendar">
                            <div class="card-body-calendar-month">Сентябрь</div>

                            <div class="calendar">
                                <div class="card-calendar-week">
                                    <div class="week">пн</div>
                                    <div class="week">вт</div>
                                    <div class="week">ср</div>
                                    <div class="week">чт</div>
                                    <div class="week">пт</div>
                                    <div class="week">сб</div>
                                    <div class="week">вс</div>
                                </div>

                                <div class="card-calendar-days" id="miniCalendarDays">
                                    <div class="day">01</div>
                                    <div class="day">02</div>
                                    <div class="day">03</div>
                                    <div class="day">04</div>
                                    <div class="day">05</div>
                                    <div class="day">06</div>
                                    <div class="day">07</div>
                                    <div class="day">08</div>
                                    <div class="day">09</div>
                                    <div class="day">10</div>
                                    <div class="day">11</div>
                                    <div class="day">12</div>
                                    <div class="day">13</div>
                                    <div class="day">14</div>
                                    <div class="day">15</div>
                                    <div class="day">16</div>
                                    <div class="day">17</div>
                                    <div class="day">18</div>
                                    <div class="day">19</div>
                                    <div class="day">20</div>
                                    <div class="day">21</div>
                                    <div class="day">22</div>
                                    <div class="day">23</div>
                                    <div class="day">24</div>
                                    <div class="day">25</div>
                                    <div class="day">26</div>
                                    <div class="day">27</div>
                                    <div class="day">28</div>
                                    <div class="day">29</div>
                                    <div class="day">30</div>

                                    <div class="day day-next">01</div>
                                    <div class="day day-next">02</div>
                                    <div class="day day-next">03</div>
                                    <div class="day day-next">04</div>
                                    <div class="day day-next">05</div>
                                </div>
                            </div>
                        </div>
                    </div>


                    <!-- КАРТОЧКА 3: ДОМАШНЕЕ ЗАДАНИЕ -->
                    <div class="card">
                        <div class="card-head">
                            <a href="/homework" style="text-decoration: none; color: inherit;">
                                <h2>Домашнее задание ➔</h2>
                            </a>
                        </div>

                        <div class="card-body">
                            <div class="card-body-input">
                                <input type="text" id="hwInput" placeholder="Добавить домашнее задание...">
                            </div>

                            <div class="card-body-tasks" id="hwContainer">
                                <p class="empty-state" style="color: #9AA0C3; font-size: 1.6rem; text-align: center; margin-top: 3rem;">Домашних заданий пока нет</p>
                            </div>
                        </div>
                    </div>


                    <!-- КАРТОЧКА 4: РАСПИСАНИЕ -->
                    <div class="card">
                        <div class="card-head">
                            <a href="/shedule" style="text-decoration: none; color: inherit;">
                                <h2>Расписание ➔</h2>
                            </a>
                        </div>

                        <div class="card-body">
                            <div class="card-body-shedule">

                                <div class="sheduleheader">
                                    <a id="prevDayBtn" style="cursor: pointer;">Вчера</a>
                                    <p class="data" id="currentDayText">20 ноября, 2025 год (Четверг)</p>
                                    <a id="nextDayBtn" style="cursor: pointer;">Завтра</a>
                                </div>
                                <div class="shedules">
                                    <div class="shedule">
                                        <div class="time">
                                            <img class="icons" src="{{ asset('media/time.svg') }}" alt="time">
                                            <p>Время: 14:15 — 15:50</p>
                                        </div>
                                        <div class="subject">
                                            <img class="icons" src="{{ asset('media/books.svg') }}" alt="subject">
                                            <p>Предмет: Проектирование и разработка интерфейсов</p>
                                        </div>

                                        <div class="classroom">
                                            <img class="icons" src="{{ asset('media/chool.svg') }}" alt="classroom">
                                            <p>Аудитория: 1-306</p>
                                        </div>

                                        <div class="teacher">
                                            <img class="icons" src="{{ asset('media/teacher.svg') }}" alt="teacher">
                                            <p>Преподаватель: Гунько Григорий Александрович</p>
                                        </div>
                                    </div>

                                    <div class="shedule">
                                        <div class="time">
                                            <img class="icons" src="{{ asset('media/time.svg') }}" alt="time">
                                            <p>Время: 16:10 — 17:45</p>
                                        </div>

                                        <div class="subject">
                                            <img class="icons" src="{{ asset('media/books.svg') }}" alt="subject">
                                            <p>Предмет: Проектирование и разработка интерфейсов</p>
                                        </div>

                                        <div class="classroom">
                                            <img class="icons" src="{{ asset('media/chool.svg') }}" alt="classroom">
                                            <p>Аудитория: 1-306</p>
                                        </div>

                                        <div class="teacher">
                                            <img class="icons" src="{{ asset('media/teacher.svg') }}" alt="teacher">
                                            <p>Преподаватель: Гунько Григорий Александрович</p>
                                        </div>
                                    </div>

                                    <div class="shedule">
                                        <div class="time">
                                            <img class="icons" src="{{ asset('media/time.svg') }}" alt="time">
                                            <p>Время: 17:55 — 19:30</p>
                                        </div>

                                        <div class="subject">
                                            <img class="icons" src="{{ asset('media/books.svg') }}" alt="subject">
                                            <p>Предмет: Базы данных и SQL</p>
                                        </div>

                                        <div class="classroom">
                                            <img class="icons" src="{{ asset('media/chool.svg') }}" alt="classroom">
                                            <p>Аудитория: 2-412</p>
                                        </div>

                                        <div class="teacher">
                                            <img class="icons" src="{{ asset('media/teacher.svg') }}" alt="teacher">
                                            <p>Преподаватель: Иванова Светлана Павловна</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>


                    <!-- КАРТОЧКА 5: ЗАМЕТКИ -->
                    <div class="card">
                        <div class="card-head">
                            <a href="/notes" style="text-decoration: none; color: inherit;">
                                <h2>Заметки ➔</h2>
                            </a>
                        </div>

                        <div class="card-body">
                            <div class="card-body-input">
                                <input type="text" id="noteInput" placeholder="Заметка...">
                            </div>

                            <div class="card-body-notes" id="notesContainer">
                                <p class="empty-state" style="color: #9AA0C3; font-size: 1.6rem; text-align: center; width: 100%; margin-top: 3rem;">Заметок пока нет</p>
                            </div>
                        </div>
                    </div>


                    <!-- КАРТОЧКА 6: МОИ ФАЙЛЫ -->
                    <div class="card">
                        <div class="card-head">
                            <a href="/privateFiels" style="text-decoration: none; color: inherit;">
                                <h2>Мои файлы ➔</h2>
                            </a>
                        </div>

                        <div class="card-body">
                            <div>
                                <button class="card-body-input-plus" id="addFileBtn">
                                    <img class="icons" src="{{ asset('media/plus.svg') }}" alt="Добавить">
                                    Добавить
                                </button>
                            </div>

                            <div class="card-body-files" id="filesContainer">
                                <p class="empty-state" style="color: #9AA0C3; font-size: 1.6rem; text-align: center; width: 100%; margin-top: 2rem;">Файлы ещё не загружены</p>
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
                            <img src="{{ asset('media/goglenew.svg') }}" alt="gogle">
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