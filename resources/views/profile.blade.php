<!DOCTYPE html>
<html lang="ru">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Профиль – SP</title>
    <link rel="icon" type="image/svg+xml" href="{{ asset('media/favicon.svg') }}">
    @vite(['resources/css/profile.css', 'resources/js/profile.js'])
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
            <div class="container"
                 id="profilePage"
                 data-avatar-url="{{ route('profile.avatar.update') }}"
                 data-avatar-delete-url="{{ route('profile.avatar.delete') }}"
                 data-info-url="{{ route('profile.info.update') }}"
                 data-password-url="{{ route('profile.password.update') }}">

                <div class="title">
                    <a href="/">
                        <img src="{{ asset('media/back.svg') }}" alt="Назад">
                    </a>
                    <p>Профиль</p>
                </div>

                <div class="profile-layout">

                    <!-- Левая колонка: визитка студента -->
                    <aside class="profile-sidebar">
                        <div class="sp-card profile-identity-card" id="avatarDropzone">
                            <div class="identity-banner"></div>

                            <div class="identity-avatar-wrap">
                                <input type="file"
                                       id="avatarInput"
                                       class="avatar-input-hidden"
                                       accept="image/jpeg,image/png,image/webp,image/gif">

                                <label for="avatarInput"
                                       class="avatar-circle"
                                       title="Нажмите или перетащите фото для загрузки">
                                    <img id="avatarImage"
                                         src="{{ $user->avatar_url }}"
                                         alt="Аватар"
                                         class="{{ $user->avatar ? 'is-custom' : '' }}">
                                    <span class="avatar-overlay">
                                        <span>Изменить</span>
                                    </span>
                                </label>
                            </div>

                            <div class="identity-body">
                                <h2 class="profile-nickname" id="profileNickname">{{ $user->username }}</h2>

                                <span class="profile-group-badge {{ $user->study_group ? '' : 'is-hidden' }}"
                                      id="profileGroupBadge">{{ $user->study_group }}</span>

                                <p class="profile-joined">На сайте с {{ $user->created_at?->format('d.m.Y') ?? '—' }}</p>

                                <div class="avatar-controls">
                                    <label for="avatarInput" class="avatar-btn-upload">Загрузить фото</label>
                                    <button type="button"
                                            id="deleteAvatarBtn"
                                            class="avatar-btn-delete"
                                            {{ $user->avatar ? '' : 'hidden' }}>Удалить</button>
                                </div>
                                <p class="avatar-hint">JPG, PNG, WEBP или GIF до 5 МБ</p>

                                <div class="stats-grid">
                                    <div class="stat-item">
                                        <span class="stat-value">{{ $stats['tasks_completed'] }}/{{ $stats['tasks_total'] }}</span>
                                        <span class="stat-label">Задач</span>
                                    </div>
                                    <div class="stat-item">
                                        <span class="stat-value">{{ $stats['notes_count'] }}</span>
                                        <span class="stat-label">Заметок</span>
                                    </div>
                                    <div class="stat-item">
                                        <span class="stat-value">{{ $stats['files_count'] }}</span>
                                        <span class="stat-label">Файлов</span>
                                    </div>
                                </div>

                                <form method="POST" action="{{ route('logout') }}" class="exit-form">
                                    @csrf
                                    <button type="submit" class="exit">Выйти из аккаунта</button>
                                </form>
                            </div>
                        </div>
                    </aside>

                    <!-- Правая колонка: карточки настроек -->
                    <div class="profile-main">

                        <!-- Карточка: Личные данные -->
                        <div class="sp-card">
                            <div class="sp-card-head">
                                <h2>Личные данные</h2>
                            </div>
                            <div class="sp-card-body">

                                <!-- Имя пользователя -->
                                <div class="info-tile" data-field="username">
                                    <div class="tile-top">
                                        <div class="tile-meta">
                                            <span class="tile-label">Имя пользователя</span>
                                            <p class="tile-value" data-field-display="username">{{ $user->username }}</p>
                                        </div>
                                        <button type="button" class="tile-edit-btn" data-edit-trigger="username">
                                            <img src="{{ asset('media/edit.svg') }}" alt="">
                                            <span>Изменить</span>
                                        </button>
                                    </div>
                                    <div class="field-editor" data-editor="username" hidden>
                                        <input type="text" class="field-input" value="{{ $user->username }}" maxlength="20" placeholder="Введите имя пользователя">
                                        <p class="field-error" data-error="username"></p>
                                        <div class="field-actions">
                                            <button type="button" class="field-save" data-save="username">Сохранить</button>
                                            <button type="button" class="field-cancel" data-cancel="username">Отмена</button>
                                        </div>
                                    </div>
                                </div>

                                <!-- Почта -->
                                <div class="info-tile" data-field="email">
                                    <div class="tile-top">
                                        <div class="tile-meta">
                                            <span class="tile-label">Электронная почта</span>
                                            <p class="tile-value" data-field-display="email">{{ $user->email }}</p>
                                        </div>
                                        <button type="button" class="tile-edit-btn" data-edit-trigger="email">
                                            <img src="{{ asset('media/edit.svg') }}" alt="">
                                            <span>Изменить</span>
                                        </button>
                                    </div>
                                    <div class="field-editor" data-editor="email" hidden>
                                        <input type="email" class="field-input" value="{{ $user->email }}" maxlength="255" placeholder="Введите email">
                                        <p class="field-error" data-error="email"></p>
                                        <div class="field-actions">
                                            <button type="button" class="field-save" data-save="email">Сохранить</button>
                                            <button type="button" class="field-cancel" data-cancel="email">Отмена</button>
                                        </div>
                                    </div>
                                </div>

                                <!-- Пароль -->
                                <div class="info-tile" data-field="password">
                                    <div class="tile-top">
                                        <div class="tile-meta">
                                            <span class="tile-label">Пароль</span>
                                            <p class="tile-value" data-field-display="password">••••••••</p>
                                        </div>
                                        <button type="button" class="tile-edit-btn" data-edit-trigger="password">
                                            <img src="{{ asset('media/edit.svg') }}" alt="">
                                            <span>Изменить</span>
                                        </button>
                                    </div>
                                    <div class="field-editor" data-editor="password" hidden>
                                        <div class="password-inputs">
                                            <input type="password" class="field-input" id="currentPassword" placeholder="Текущий пароль" autocomplete="current-password">
                                            <input type="password" class="field-input" id="newPassword" placeholder="Новый пароль (минимум 6 символов)" autocomplete="new-password">
                                            <input type="password" class="field-input" id="newPasswordConfirm" placeholder="Повторите новый пароль" autocomplete="new-password">
                                        </div>
                                        <p class="field-error" data-error="password"></p>
                                        <div class="field-actions">
                                            <button type="button" class="field-save" data-save="password">Обновить пароль</button>
                                            <button type="button" class="field-cancel" data-cancel="password">Отмена</button>
                                        </div>
                                    </div>
                                </div>

                                <!-- Учебная группа -->
                                <div class="info-tile" data-field="study_group">
                                    <div class="tile-top">
                                        <div class="tile-meta">
                                            <span class="tile-label">Учебная группа</span>
                                            <p class="tile-value {{ $user->study_group ? '' : 'is-empty' }}"
                                               data-field-display="study_group"
                                               data-placeholder="Не указано">{{ $user->study_group ?: 'Не указано' }}</p>
                                        </div>
                                        <button type="button" class="tile-edit-btn" data-edit-trigger="study_group">
                                            <img src="{{ asset('media/edit.svg') }}" alt="">
                                            <span>Изменить</span>
                                        </button>
                                    </div>
                                    <div class="field-editor" data-editor="study_group" hidden>
                                        <input type="text" class="field-input" value="{{ $user->study_group ?? '' }}" maxlength="100" placeholder="432">
                                        <p class="field-error" data-error="study_group"></p>
                                        <div class="field-actions">
                                            <button type="button" class="field-save" data-save="study_group">Сохранить</button>
                                            <button type="button" class="field-cancel" data-cancel="study_group">Отмена</button>
                                        </div>
                                    </div>
                                </div>

                                <!-- О себе -->
                                <div class="info-tile" data-field="bio">
                                    <div class="tile-top">
                                        <div class="tile-meta">
                                            <span class="tile-label">О себе / Цель на семестр</span>
                                            <p class="tile-value {{ $user->bio ? '' : 'is-empty' }}"
                                               data-field-display="bio"
                                               data-placeholder="Расскажите немного о себе">{{ $user->bio ?: 'Расскажите немного о себе' }}</p>
                                        </div>
                                        <button type="button" class="tile-edit-btn" data-edit-trigger="bio">
                                            <img src="{{ asset('media/edit.svg') }}" alt="">
                                            <span>Изменить</span>
                                        </button>
                                    </div>
                                    <div class="field-editor" data-editor="bio" hidden>
                                        <textarea class="field-input field-textarea" rows="3" maxlength="300" placeholder="Например: Готовлюсь к сессии, учу Laravel">{{ $user->bio ?? '' }}</textarea>
                                        <p class="field-error" data-error="bio"></p>
                                        <div class="field-actions">
                                            <button type="button" class="field-save" data-save="bio">Сохранить</button>
                                            <button type="button" class="field-cancel" data-cancel="bio">Отмена</button>
                                        </div>
                                    </div>
                                </div>

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

        <div class="profile-toast" id="profileToast" role="status" aria-live="polite"></div>
    </main>
</body>

</html>
