<!DOCTYPE html>
<html lang="ru">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Главная – SP</title>
    <link rel="icon" type="image/svg+xml" href="{{ asset('media/favicon.svg') }}">
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

            @include('partials.header-avatar')
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
                            <a href="/calendar">
                                <h2>Календарь ➔</h2>
                            </a>
                        </div>

                        <div class="card-body-calendar">
                            <div class="card-body-calendar-header">
                                <button type="button" class="calendar-btn" id="miniCalPrevBtn">‹</button>
                                <div class="calendar-selects">
                                    <select class="calendar-select" id="miniCalMonthSelect" aria-label="Выбор месяца"></select>
                                    <select class="calendar-select" id="miniCalYearSelect" aria-label="Выбор года"></select>
                                </div>
                                <button type="button" class="calendar-btn" id="miniCalNextBtn">›</button>
                            </div>

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

                                <div class="card-calendar-days" id="miniCalendarDays"></div>
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

                            <div class="card-body-tasks"
                                 id="hwContainer"
                                 data-homeworks='@json($homeworksData ?? [])'
                                 data-store-url="{{ route('homework.store') }}">
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
                                    <p class="data" id="currentDayText">18 ноября, 2026 год (Среда)</p>
                                    <a id="nextDayBtn" style="cursor: pointer;">Завтра</a>
                                </div>
                                <div class="shedules" id="homeScheduleList"
                                    data-lessons='@json($scheduleData ?? [])'
                                    data-time-icon="{{ asset('media/time.svg') }}"
                                    data-books-icon="{{ asset('media/books.svg') }}"
                                    data-classroom-icon="{{ asset('media/chool.svg') }}"
                                    data-teacher-icon="{{ asset('media/teacher.svg') }}">
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
                                @forelse($notes ?? [] as $note)
                                    <a href="/notes" class="card-body-notes-note">
                                        <h3>{{ $note->title ?: 'Без названия' }}</h3>
                                        <p>{{ Str::limit(trim(strip_tags($note->content)), 70, '...') ?: 'Пустая заметка' }}</p>
                                    </a>
                                @empty
                                    <p class="empty-state" style="color: #9AA0C3; font-size: 1.6rem; text-align: center; width: 100%; margin-top: 3rem;">Заметок пока нет</p>
                                @endforelse
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
                                <input type="file" id="homeFileInput" hidden multiple>
                                <button type="button" class="card-body-input-plus" id="addFileBtn">
                                    <img class="icons" src="{{ asset('media/plus.svg') }}" alt="Добавить">
                                    Добавить
                                </button>
                            </div>

                            <div class="card-body-files"
                                 id="filesContainer"
                                 data-upload-url="{{ route('private-files.store') }}"
                                 data-file-icon="{{ asset('media/fiels.svg') }}"
                                 data-download-icon="{{ asset('media/download.svg') }}"
                                 data-delete-icon="{{ asset('media/delete.svg') }}">
                                @forelse($files ?? [] as $file)
                                    <div class="onefile" data-file-id="{{ $file->id }}" data-file-name="{{ $file->original_name }}">
                                        <img class="icons" src="{{ asset('media/fiels.svg') }}" alt="file">
                                        <p class="onefile-name" title="{{ $file->original_name }}">{{ $file->original_name }}</p>
                                        <div class="onefile-actions">
                                            <a href="{{ route('private-files.download', $file) }}" class="onefile-action-btn" title="Скачать">
                                                <img class="icons" src="{{ asset('media/download.svg') }}" alt="Скачать">
                                            </a>
                                            <button type="button" class="onefile-action-btn onefile-delete-btn" data-delete-url="{{ route('private-files.destroy', $file) }}" title="Удалить">
                                                <img class="icons" src="{{ asset('media/delete.svg') }}" alt="Удалить">
                                            </button>
                                        </div>
                                    </div>
                                @empty
                                    <p class="empty-state" style="color: #9AA0C3; font-size: 1.6rem; text-align: center; width: 100%; margin-top: 2rem;">Файлы ещё не загружены</p>
                                @endforelse
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