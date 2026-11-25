<!DOCTYPE html>
<html lang="ru">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Задачи – SP</title>
    <link rel="icon" type="image/svg+xml" href="{{ asset('media/favicon.svg') }}">
    @vite(['resources/css/task.css', 'resources/js/task.js'])
</head>

<body>
    <header>
        <nav>
            <a href="/">
                <img class="logotip" src="{{ asset('media/newlogo.svg') }}" alt="Логотип">
            </a>
            <a href="/">Главная</a>
            <a href="/task" class="active">Задачи</a>
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
                <!-- Заголовок страницы со стрелкой назад -->
                <div class="title">
                    <a href="/" title="На главную">
                        <img src="{{ asset('media/back.svg') }}" alt="Назад">
                    </a>
                    <p>Задачи</p>
                </div>

                <!-- Верхний блок создания новой задачи -->
                <div class="task-create-card">
                    <form method="POST" action="{{ route('tasks.store') }}" id="addTaskForm" class="task-create-form">
                        @csrf
                        <div class="task-create-input-wrapper">
                            <input type="text" name="task_text" id="taskTextInput" placeholder="Написать новую задачу..." required autocomplete="off">
                        </div>

                        <div class="task-create-actions">
                            <div class="task-deadline-picker" id="deadlinePickerBtn" title="Выбрать срок сдачи">
                                <img src="{{ asset('media/calendar.svg') }}" alt="Календарь" class="deadline-icon">
                                <input type="date" name="deadline" id="deadlineDate" class="hidden-date-input">
                                <span class="deadline-text" id="deadlineDisplay">Срок сдачи</span>
                                <button type="button" class="deadline-clear-btn" id="deadlineClearBtn" title="Сбросить срок">&times;</button>
                            </div>

                            <button type="submit" class="task-submit-btn" id="addTaskSubmitBtn">
                                <img src="{{ asset('media/plus.svg') }}" alt="Плюс">
                                <span>Добавить</span>
                            </button>
                        </div>
                    </form>
                </div>

                <!-- Панель управления и фильтрации -->
                <div class="tasks-toolbar">
                    <div class="tasks-filters">
                        <button type="button" class="tasks-filter-btn active" data-filter="todo">
                            К выполнению (<span id="countTodo">{{ $tasks->where('status', 'todo')->count() }}</span>)
                        </button>
                        <button type="button" class="tasks-filter-btn" data-filter="in_progress">
                            В работе (<span id="countInProgress">{{ $tasks->where('status', 'in_progress')->count() }}</span>)
                        </button>
                        <button type="button" class="tasks-filter-btn" data-filter="completed">
                            Выполненные (<span id="countCompleted">{{ $tasks->where('status', 'completed')->count() }}</span>)
                        </button>
                        <button type="button" class="tasks-filter-btn" data-filter="all">
                            Все (<span id="countAll">{{ $tasks->count() }}</span>)
                        </button>
                    </div>

                    <div class="tasks-stats">
                        Выполнено: <strong id="progressText">{{ $tasks->where('status', 'completed')->count() }} из {{ $tasks->count() }}</strong>
                    </div>
                </div>

                <!-- Список задач (карточки с переключением статусов) -->
                <div class="tasks-list" id="tasksContainer">
                    @forelse($tasks as $task)
                        @php
                            $status = $task->status ?? ($task->is_completed ? 'completed' : 'todo');
                        @endphp
                        <div class="task-item {{ $status === 'completed' ? 'is-completed' : '' }} {{ $status === 'in_progress' ? 'is-in-progress' : '' }}" 
                             data-id="{{ $task->id }}" 
                             data-status="{{ $status }}">
                            
                            <div class="task-checkbox-wrapper">
                                <input type="checkbox" id="task_{{ $task->id }}" class="task-checkbox" {{ $status === 'completed' ? 'checked' : '' }} title="{{ $status === 'completed' ? 'Вернуть в список' : 'Отметить как выполненную' }}">
                            </div>

                            <div class="task-body">
                                <div class="task-title-row">
                                    @if($status === 'in_progress')
                                        <span class="task-status-badge in-progress">В работе</span>
                                    @endif
                                    <label for="task_{{ $task->id }}" class="task-text" title="{{ $task->title }}">
                                        {{ $task->title }}
                                    </label>
                                </div>
                            </div>

                            <div class="task-meta">
                                @if($task->deadline)
                                    <div class="task-deadline-badge" title="Срок сдачи">
                                        <img src="{{ asset('media/calendar.svg') }}" alt="Календарь">
                                        <span>до {{ \Carbon\Carbon::parse($task->deadline)->format('d.m.y') }}</span>
                                    </div>
                                @endif

                                <!-- Кнопка смены статуса (В работу / Отложить) -->
                                <div class="task-action-buttons">
                                    @if($status === 'todo')
                                        <button type="button" class="task-btn-status start" data-action="start" title="Взять в работу прямо сейчас">
                                            <span>В работу</span>
                                        </button>
                                    @elseif($status === 'in_progress')
                                        <button type="button" class="task-btn-status pause" data-action="pause" title="Отложить обратно в список">
                                            <span>Отложить</span>
                                        </button>
                                    @elseif($status === 'completed')
                                        <button type="button" class="task-btn-status restart" data-action="restart" title="Вернуть задачу в список">
                                            <span>Вернуть</span>
                                        </button>
                                    @endif
                                </div>

                                <button type="button" class="task-delete-btn" title="Удалить задачу" data-id="{{ $task->id }}">
                                    <img src="{{ asset('media/delete.svg') }}" alt="Удалить">
                                </button>
                            </div>
                        </div>
                    @empty
                        <div class="empty-state" id="emptyState">
                            <p>У вас пока нет задач. Добавьте первую задачу в строке выше!</p>
                        </div>
                    @endforelse
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
                            <img src="{{ asset('media/goglenew.svg') }}" alt="Google">
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
