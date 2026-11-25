<!DOCTYPE html>
<html lang="ru">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Новое ДЗ – SP</title>
    <link rel="icon" type="image/svg+xml" href="{{ asset('media/favicon.svg') }}">
    @vite(['resources/css/homeworkNew.css', 'resources/js/homeworkNew.js'])
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
                    <a href="/homework" id="backBtn" title="Назад к списку ДЗ">
                        <img src="{{ asset('media/back.svg') }}" alt="Назад">
                    </a>
                    <p>Добавить домашнее задание</p>
                </div>

                <div class="create-homework-card">
                    <form id="newHomeworkForm"
                          data-store-url="{{ route('homework.store') }}"
                          data-subjects='@json($subjects ?? [])'>
                        <div class="create-homework">
                            <!-- Поле: Предмет -->
                            <div class="one-section">
                                <label for="hwSubjectInput">Предмет</label>
                                <div class="new-homework-input">
                                    <input type="text" id="hwSubjectInput" placeholder="Например, Математика" required>
                                </div>
                            </div>

                            <!-- Поле: Текст задания -->
                            <div class="one-section">
                                <label for="hwTextInput">Задание</label>
                                <div class="new-homework-input">
                                    <input type="text" id="hwTextInput" placeholder="Что нужно сделать..." required>
                                </div>
                            </div>

                            <!-- Поле: Срок сдачи -->
                            <div class="one-section">
                                <label for="hwDeadlineInput">Срок сдачи</label>
                                <div class="new-homework-input date-input-wrapper">
                                    <input type="text" id="hwDeadlineInput" placeholder="ДД.ММ.ГГ" maxlength="8" autocomplete="off">
                                    <input type="date" id="hwDatePicker" class="date-picker-native" tabindex="-1">
                                    <img src="{{ asset('media/calendar.svg') }}" alt="calendar" id="calendarIcon" title="Выбрать дату в календаре">
                                </div>
                            </div>
                        </div>

                        <!-- Блок загрузки файла -->
                        <div class="upload-file">
                            <span class="upload-file-text">Прикрепить файл:</span>
                            <div class="upload-file-input">
                                <input type="file" id="hwFileInput" class="file-input-hidden">
                                <label for="hwFileInput" class="upload-file-button">
                                    <img src="{{ asset('media/file.svg') }}" alt="upload">
                                    <span id="fileNameLabel">Выбрать файл</span>
                                </label>
                            </div>
                        </div>

                        <!-- Кнопка Добавить -->
                        <div class="create-button">
                            <button type="submit" id="addHwSubmitBtn" class="add-task-button">
                                Сохранить домашнее задание
                            </button>
                        </div>
                    </form>
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
