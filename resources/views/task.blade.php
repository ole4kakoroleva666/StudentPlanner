<!DOCTYPE html>
<html lang="ru">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Задачи - StudentPlanner</title>
    @vite(['resources/css/task.css', 'resources/js/task.js'])
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
            <a href="/profile">Моё</a>
            
            <a href="/profile">
                <img src="{{ asset('media/Profile.svg') }}" alt="Профиль">
            </a>
        </nav>
    </header>

    <main>
        <section>
            <div class="container">
                <div class="title">
                    <a href="/">
                        <img src="{{ asset('media/back.svg') }}" alt="Назад">
                    </a>
                    <p>Задачи</p>
                </div>

                <div class="tasks-two-columns">
                    <!-- Левая колонка: Список задач -->
                    <div class="tasks-left">
                        <div class="all-tasks" id="tasksContainer">
                            @forelse($tasks as $task)
                                <div class="all-tasks-task" data-id="{{ $task->id }}">
                                    <input type="checkbox" id="task_{{ $task->id }}" {{ $task->is_completed ? 'checked' : '' }}>
                                    <div class="task-text-content {{ $task->deadline ? 'has-deadline' : '' }}">
                                        <div class="task-title-wrapper">
                                            <label for="task_{{ $task->id }}" class="task-title" title="{{ $task->title }}">
                                                {{ $task->title }}
                                            </label>
                                        </div>
                                        @if($task->deadline)
                                            <p class="task-deadline">до {{ \Carbon\Carbon::parse($task->deadline)->format('d.m.y') }}</p>
                                        @endif
                                    </div>
                                    <div class="all-tasks-task-line"></div>
                                </div>
                            @empty
                                <p class="empty-state" style="color: #9AA0C3; font-size: 1.6rem; text-align: center; margin-top: 3rem;">
                                    У вас пока нет задач. Добавьте первую задачу в форме справа!
                                </p>
                            @endforelse
                        </div>
                    </div>

                    <!-- Правая колонка: Форма добавления задачи -->
                    <div class="tasks-right">
                        <form method="POST" action="{{ route('tasks.store') }}" class="add-task-form" id="addTaskForm">
                            @csrf
                            
                            <div class="new-task-input">
                                <input type="text" name="task_text" id="taskTextInput" placeholder="Написать задачу..." required autocomplete="off">
                            </div>

                            <div class="deadline-section">
                                <div class="deadline-label">Срок сдачи</div>
                                <div class="deadline-input-wrapper">
                                    <label for="deadlineDate" class="deadline-icon-button" style="cursor: pointer;">
                                        <img src="{{ asset('media/calendar.svg') }}" alt="Календарь">
                                    </label>
                                    <input type="date" name="deadline" id="deadlineDate" style="position: absolute; opacity: 0; pointer-events: none; width: 0; height: 0;">
                                    <div class="deadline-display" id="deadlineDisplay" style="cursor: pointer;" title="Нажмите, чтобы выбрать срок сдачи">ДД.ММ.ГГ</div>
                                </div>
                            </div>

                            <button type="submit" class="add-task-button" id="addTaskSubmitBtn">Добавить</button>
                        </form>
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
