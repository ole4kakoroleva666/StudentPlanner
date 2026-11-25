<!DOCTYPE html>
<html lang="ru">

@php
    $files = $files ?? collect();

    $formatSize = function (int $bytes): string {
        if ($bytes < 1024) {
            return $bytes . ' Б';
        }
        if ($bytes < 1048576) {
            return round($bytes / 1024, 1) . ' КБ';
        }
        return round($bytes / 1048576, 1) . ' МБ';
    };
@endphp

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Личные файлы – SP</title>
    <link rel="icon" type="image/svg+xml" href="{{ asset('media/favicon.svg') }}">
    @vite(['resources/css/privateFiels.css', 'resources/js/privateFiels.js'])
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
                    <a href="/">
                        <img src="{{ asset('media/back.svg') }}" alt="Назад">
                    </a>
                    <p>Личные файлы</p>
                </div>

                <!-- Блок загрузки: клик по папке открывает выбор файла -->
                <div class="upload-card" id="uploadDropzone">
                    <input type="file" id="file-upload" class="file-input-hidden" multiple>
                    <label for="file-upload" class="upload-folder-trigger" id="fileUploadLabel" title="Выбрать файл для загрузки">
                        <span class="upload-folder-icon">
                            <img src="{{ asset('media/file.svg') }}" alt="Загрузить файл">
                        </span>
                        <span class="upload-folder-info">
                            <span class="upload-folder-title">Загрузить файл</span>
                            <span class="upload-folder-hint">Нажмите на папку или перетащите файл сюда (до 20 МБ)</span>
                        </span>
                    </label>
                    <span class="upload-file-status" id="uploadFileStatus" aria-live="polite"></span>
                </div>

                <h2 class="files-subtitle">Мои файлы</h2>

                <!-- Список личных файлов -->
                <div class="main-private-fiels"
                     id="filesList"
                     data-upload-url="{{ route('private-files.store') }}"
                     data-download-icon="{{ asset('media/download.svg') }}"
                     data-delete-icon="{{ asset('media/delete.svg') }}"
                     data-file-icon="{{ asset('media/fiels.svg') }}"
                     data-current-page="{{ method_exists($files, 'currentPage') ? $files->currentPage() : 1 }}"
                     data-per-page="10">

                    @forelse($files as $file)
                        <div class="file-row" data-file-id="{{ $file->id }}" data-file-name="{{ $file->original_name }}">
                            <div class="one-private-fiels">
                                <div class="private-fiels-icon">
                                    <img src="{{ asset('media/fiels.svg') }}" alt="Файл">
                                </div>
                                <div class="private-fiels-meta">
                                    <span class="private-fiels-name" title="{{ $file->original_name }}">{{ $file->original_name }}</span>
                                    <span class="private-fiels-details">
                                        <span>{{ $file->created_at?->format('d.m.y') }}</span>
                                        <span class="dot-sep">•</span>
                                        <span>{{ $formatSize((int) $file->size) }}</span>
                                    </span>
                                </div>
                                <div class="private-fiels-buttons">
                                    <a href="{{ route('private-files.download', $file) }}"
                                       class="private-fiels-download"
                                       title="Скачать">
                                        <img src="{{ asset('media/download.svg') }}" alt="Скачать">
                                    </a>
                                    <button type="button"
                                            class="private-fiels-delete"
                                            data-delete-url="{{ route('private-files.destroy', $file) }}"
                                            title="Удалить">
                                        <img src="{{ asset('media/delete.svg') }}" alt="Удалить">
                                    </button>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="files-empty-state" id="filesEmptyState">
                            <img src="{{ asset('media/fiels.svg') }}" alt="" class="files-empty-icon">
                            <p>У вас пока нет загруженных файлов</p>
                            <span>Нажмите на папку выше, чтобы добавить первый документ</span>
                        </div>
                    @endforelse
                </div>

                @if(method_exists($files, 'hasPages') && $files->hasPages())
                    <nav class="files-pagination" aria-label="Навигация по страницам">
                        @if($files->onFirstPage())
                            <span class="page-btn is-disabled" aria-disabled="true" title="Предыдущая страница">&lsaquo;</span>
                        @else
                            <a href="{{ $files->previousPageUrl() }}" class="page-btn" rel="prev" title="Предыдущая страница">&lsaquo;</a>
                        @endif

                        @for($page = 1; $page <= $files->lastPage(); $page++)
                            @if($page === $files->currentPage())
                                <span class="page-btn is-active" aria-current="page">{{ $page }}</span>
                            @elseif(
                                $page === 1 ||
                                $page === $files->lastPage() ||
                                abs($page - $files->currentPage()) <= 1
                            )
                                <a href="{{ $files->url($page) }}" class="page-btn">{{ $page }}</a>
                            @elseif(
                                ($page === 2 && $files->currentPage() > 3) ||
                                ($page === $files->lastPage() - 1 && $files->currentPage() < $files->lastPage() - 2)
                            )
                                <span class="page-dots">...</span>
                            @endif
                        @endfor

                        @if($files->hasMorePages())
                            <a href="{{ $files->nextPageUrl() }}" class="page-btn" rel="next" title="Следующая страница">&rsaquo;</a>
                        @else
                            <span class="page-btn is-disabled" aria-disabled="true" title="Следующая страница">&rsaquo;</span>
                        @endif
                    </nav>
                @endif
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
