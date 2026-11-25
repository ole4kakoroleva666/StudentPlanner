<!DOCTYPE html>
<html lang="ru">

@php
    $pinnedNotes = $pinnedNotes ?? collect();
    $otherNotes = $otherNotes ?? collect();
@endphp

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Заметки – SP</title>
    <link rel="icon" type="image/svg+xml" href="{{ asset('media/favicon.svg') }}">
    @vite(['resources/css/notes.css', 'resources/js/notes.js'])
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
                    <p>Заметки</p>
                </div>

                <!-- Компактная строка создания заметки -->
                <div class="note-creator-wrapper" id="noteCreatorWrapper">
                    <div class="notes-input" id="notesInputTrigger" tabindex="0" role="button" aria-label="Создать заметку">
                        <span>Заметка...</span>
                    </div>

                    <!-- Раскрытая форма создания заметки -->
                    <form class="note-creator-card hidden" id="noteCreateForm" enctype="multipart/form-data">
                        @csrf
                        <input type="hidden" name="is_pinned" id="createIsPinnedInput" value="0">
                        <div class="note-creator-header">
                            <input type="text" name="title" id="createNoteTitle" class="note-title-input" placeholder="Название" autocomplete="off">
                            <button type="button" class="pin-btn" id="createPinBtn" title="Закрепить заметку" aria-pressed="false">
                                <img src="{{ asset('media/pin.svg') }}" alt="Закрепить">
                            </button>
                        </div>

                        <div class="note-editor" id="createNoteEditor" contenteditable="true" data-placeholder="Заметка..."></div>

                        <div class="note-attachment-preview hidden" id="createAttachmentPreview">
                            <span class="attachment-chip">
                                <span class="attachment-chip-name" id="createAttachmentName"></span>
                                <button type="button" class="attachment-remove-btn" id="createAttachmentRemove" title="Убрать файл">&times;</button>
                            </span>
                        </div>

                        <div class="note-toolbar" id="createNoteToolbar">
                            <div class="toolbar-buttons">
                                <button type="button" class="tb-btn tb-normal" data-cmd="normal" title="Обычный текст (сбросить стиль)">Aa</button>
                                <button type="button" class="tb-btn tb-underline" data-cmd="underline" title="Подчёркнутый">U</button>
                                <button type="button" class="tb-btn tb-bold" data-cmd="bold" title="Жирный">B</button>
                                <button type="button" class="tb-btn tb-italic" data-cmd="italic" title="Курсив">I</button>
                                <button type="button" class="tb-btn tb-h1" data-cmd="h1" title="Заголовок 1">H1</button>
                                <button type="button" class="tb-btn tb-h2" data-cmd="h2" title="Заголовок 2">H2</button>
                                <button type="button" class="tb-btn tb-attach" data-action="attach" title="Прикрепить файл">
                                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M21.44 11.05l-9.19 9.19a6 6 0 0 1-8.49-8.49l9.19-9.19a4 4 0 0 1 5.66 5.66l-9.2 9.19a2 2 0 0 1-2.83-2.83l8.49-8.48"/>
                                    </svg>
                                </button>
                                <button type="button" class="tb-btn tb-list" data-cmd="insertUnorderedList" title="Список">
                                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round">
                                        <line x1="9" y1="6" x2="20" y2="6"/><line x1="9" y1="12" x2="20" y2="12"/><line x1="9" y1="18" x2="20" y2="18"/>
                                        <circle cx="4.5" cy="6" r="1.2" fill="currentColor"/><circle cx="4.5" cy="12" r="1.2" fill="currentColor"/><circle cx="4.5" cy="18" r="1.2" fill="currentColor"/>
                                    </svg>
                                </button>
                                <button type="button" class="tb-btn tb-undo" data-cmd="undo" title="Отменить">
                                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M9 14L4 9l5-5"/><path d="M4 9h10.5a5.5 5.5 0 0 1 0 11H11"/>
                                    </svg>
                                </button>
                                <button type="button" class="tb-btn tb-redo" data-cmd="redo" title="Повторить">
                                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M15 14l5-5-5-5"/><path d="M20 9H9.5a5.5 5.5 0 0 0 0 11H13"/>
                                    </svg>
                                </button>
                            </div>
                            <input type="file" name="attachment" id="createNoteFileInput" class="hidden">
                            <button type="submit" class="btn-add-note" id="createSubmitBtn">Добавить</button>
                        </div>
                    </form>
                </div>

                <!-- Закрепленные заметки -->
                <div class="notes-user {{ $pinnedNotes->isEmpty() ? 'hidden' : '' }}" id="pinnedSection">
                    <p>Закрепленные</p>
                    <div class="notes" id="pinnedNotesGrid">
                        @foreach($pinnedNotes as $note)
                            <div class="one-notes"
                                 data-id="{{ $note->id }}"
                                 data-title="{{ $note->title ?? '' }}"
                                 data-content="{{ $note->content ?? '' }}"
                                 data-pinned="1"
                                 data-attachment-url="{{ $note->attachment_path ? asset($note->attachment_path) : '' }}"
                                 data-attachment-name="{{ $note->attachment_name ?? '' }}">
                                <div class="name">
                                    <p>{{ $note->title ?: 'Без названия' }}</p>
                                    <button type="button" class="card-pin-btn pinned" title="Открепить">
                                        <img src="{{ asset('media/pin.svg') }}" alt="pin">
                                    </button>
                                </div>
                                <div class="note-card-body">{!! $note->content !!}</div>
                                @if($note->attachment_name)
                                    <div class="note-card-attachment">
                                        <span>{{ $note->attachment_name }}</span>
                                    </div>
                                @endif
                            </div>
                        @endforeach
                    </div>
                </div>

                <!-- Другие заметки -->
                <div class="notes-user" id="otherSection">
                    <p id="otherSectionLabel" class="{{ $pinnedNotes->isEmpty() && $otherNotes->isEmpty() ? 'hidden' : '' }}">Другие</p>
                    <div class="notes" id="otherNotesGrid">
                        @foreach($otherNotes as $note)
                            <div class="one-notes"
                                 data-id="{{ $note->id }}"
                                 data-title="{{ $note->title ?? '' }}"
                                 data-content="{{ $note->content ?? '' }}"
                                 data-pinned="0"
                                 data-attachment-url="{{ $note->attachment_path ? asset($note->attachment_path) : '' }}"
                                 data-attachment-name="{{ $note->attachment_name ?? '' }}">
                                <div class="name">
                                    <p>{{ $note->title ?: 'Без названия' }}</p>
                                    <button type="button" class="card-pin-btn" title="Закрепить">
                                        <img src="{{ asset('media/pin.svg') }}" alt="pin">
                                    </button>
                                </div>
                                <div class="note-card-body">{!! $note->content !!}</div>
                                @if($note->attachment_name)
                                    <div class="note-card-attachment">
                                        <span>{{ $note->attachment_name }}</span>
                                    </div>
                                @endif
                            </div>
                        @endforeach
                    </div>

                    <p class="notes-empty-state {{ ($pinnedNotes->isNotEmpty() || $otherNotes->isNotEmpty()) ? 'hidden' : '' }}" id="notesEmptyState">
                        У вас пока нет заметок. Нажмите на «Заметка...» выше, чтобы создать первую!
                    </p>
                </div>
            </div>

            <!-- Модальное окно просмотра и редактирования заметки -->
            <div class="note-modal-backdrop hidden" id="noteModalBackdrop" aria-hidden="true">
                <div class="note-modal" role="dialog" aria-modal="true">
                    <!-- Режим просмотра -->
                    <div class="note-modal-view" id="noteModalView">
                        <div class="note-modal-header">
                            <h2 class="note-modal-title" id="modalViewTitle"></h2>
                            <button type="button" class="pin-btn" id="modalViewPinBtn" title="Закрепить">
                                <img src="{{ asset('media/pin.svg') }}" alt="Закрепить">
                            </button>
                        </div>

                        <div class="note-modal-body" id="modalViewBody"></div>

                        <div class="note-modal-attachment hidden" id="modalViewAttachment">
                            <a href="#" target="_blank" rel="noopener" class="attachment-link" id="modalViewAttachmentLink">
                                <span id="modalViewAttachmentName"></span>
                            </a>
                        </div>

                        <div class="note-modal-footer">
                            <button type="button" class="modal-icon-btn modal-delete-btn" id="modalDeleteBtn" title="Удалить заметку">
                                <img src="{{ asset('media/delete.svg') }}" alt="Удалить">
                            </button>
                            <button type="button" class="modal-icon-btn modal-edit-btn" id="modalEditBtn" title="Редактировать заметку">
                                <img src="{{ asset('media/edit.svg') }}" alt="Редактировать">
                            </button>
                        </div>
                    </div>

                    <!-- Режим редактирования -->
                    <form class="note-modal-edit hidden" id="noteModalEditForm" enctype="multipart/form-data">
                        <div class="note-creator-header">
                            <input type="text" name="title" id="editNoteTitle" class="note-title-input" placeholder="Название" autocomplete="off">
                            <button type="button" class="pin-btn" id="editPinBtn" title="Закрепить заметку">
                                <img src="{{ asset('media/pin.svg') }}" alt="Закрепить">
                            </button>
                        </div>

                        <div class="note-editor note-modal-editor" id="editNoteEditor" contenteditable="true" data-placeholder="Заметка..."></div>

                        <div class="note-attachment-preview hidden" id="editAttachmentPreview">
                            <span class="attachment-chip">
                                <span class="attachment-chip-name" id="editAttachmentName"></span>
                                <button type="button" class="attachment-remove-btn" id="editAttachmentRemove" title="Убрать файл">&times;</button>
                            </span>
                        </div>

                        <div class="note-toolbar" id="editNoteToolbar">
                            <div class="toolbar-buttons">
                                <button type="button" class="tb-btn tb-normal" data-cmd="normal" title="Обычный текст (сбросить стиль)">Aa</button>
                                <button type="button" class="tb-btn tb-underline" data-cmd="underline" title="Подчёркнутый">U</button>
                                <button type="button" class="tb-btn tb-bold" data-cmd="bold" title="Жирный">B</button>
                                <button type="button" class="tb-btn tb-italic" data-cmd="italic" title="Курсив">I</button>
                                <button type="button" class="tb-btn tb-h1" data-cmd="h1" title="Заголовок 1">H1</button>
                                <button type="button" class="tb-btn tb-h2" data-cmd="h2" title="Заголовок 2">H2</button>
                                <button type="button" class="tb-btn tb-attach" data-action="attach" title="Прикрепить файл">
                                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M21.44 11.05l-9.19 9.19a6 6 0 0 1-8.49-8.49l9.19-9.19a4 4 0 0 1 5.66 5.66l-9.2 9.19a2 2 0 0 1-2.83-2.83l8.49-8.48"/>
                                    </svg>
                                </button>
                                <button type="button" class="tb-btn tb-list" data-cmd="insertUnorderedList" title="Список">
                                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round">
                                        <line x1="9" y1="6" x2="20" y2="6"/><line x1="9" y1="12" x2="20" y2="12"/><line x1="9" y1="18" x2="20" y2="18"/>
                                        <circle cx="4.5" cy="6" r="1.2" fill="currentColor"/><circle cx="4.5" cy="12" r="1.2" fill="currentColor"/><circle cx="4.5" cy="18" r="1.2" fill="currentColor"/>
                                    </svg>
                                </button>
                                <button type="button" class="tb-btn tb-undo" data-cmd="undo" title="Отменить">
                                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M9 14L4 9l5-5"/><path d="M4 9h10.5a5.5 5.5 0 0 1 0 11H11"/>
                                    </svg>
                                </button>
                                <button type="button" class="tb-btn tb-redo" data-cmd="redo" title="Повторить">
                                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M15 14l5-5-5-5"/><path d="M20 9H9.5a5.5 5.5 0 0 0 0 11H13"/>
                                    </svg>
                                </button>
                            </div>
                            <input type="file" name="attachment" id="editNoteFileInput" class="hidden">
                            <div class="edit-actions">
                                <button type="button" class="btn-cancel-note" id="editCancelBtn">Отмена</button>
                                <button type="submit" class="btn-add-note" id="editSaveBtn">Сохранить</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            </div>
        </section>
    </main>

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
            © StudyOrganizerOlya
        </div>
    </footer>
</body>

</html>
