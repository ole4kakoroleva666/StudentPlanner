@php
    $headerUser = auth()->user();
    $hasHeaderAvatar = $headerUser && $headerUser->avatar;
@endphp
<div class="header-actions">
    @if($headerUser)
        <div class="notif-wrapper" id="notifWrapper">
            <button type="button" class="notif-btn" id="notifToggleBtn" title="Уведомления" aria-expanded="false">
                <img src="{{ asset('media/reminder.svg') }}" alt="Уведомления">
                <span class="notif-badge" id="notifBadge" hidden>0</span>
            </button>

            <div class="notif-dropdown" id="notifDropdown" hidden>
                <div class="notif-dropdown-head">
                    <span>Уведомления</span>
                    <button type="button" class="notif-read-all" id="notifReadAllBtn">Прочитать все</button>
                </div>
                <div class="notif-list" id="notifList">
                    <div class="notif-empty">Загрузка...</div>
                </div>
            </div>
        </div>
    @endif

    <a href="/profile" class="header-profile-link" title="Профиль">
        <img src="{{ $hasHeaderAvatar ? $headerUser->avatar_url : asset('media/Profile.svg') }}"
             alt="Профиль"
             class="{{ $hasHeaderAvatar ? 'header-avatar' : '' }}"
             data-header-avatar
             data-default-src="{{ asset('media/Profile.svg') }}">
    </a>

    <button type="button" class="burger-btn" id="burgerToggleBtn" aria-label="Меню" aria-expanded="false">
        <span class="burger-line"></span>
        <span class="burger-line"></span>
        <span class="burger-line"></span>
    </button>
</div>

<script>
    (() => {
        const burgerBtn = document.getElementById('burgerToggleBtn');
        const headerEl = burgerBtn?.closest('header');
        if (!burgerBtn || !headerEl) return;

        burgerBtn.addEventListener('click', (e) => {
            e.stopPropagation();
            const isOpen = headerEl.classList.toggle('is-menu-open');
            burgerBtn.setAttribute('aria-expanded', String(isOpen));
        });

        document.addEventListener('click', (e) => {
            if (!headerEl.contains(e.target)) {
                headerEl.classList.remove('is-menu-open');
                burgerBtn.setAttribute('aria-expanded', 'false');
            }
        });
    })();
</script>

@if($headerUser)
<script>
    (() => {
        const wrapper = document.getElementById('notifWrapper');
        const toggleBtn = document.getElementById('notifToggleBtn');
        const dropdown = document.getElementById('notifDropdown');
        const badge = document.getElementById('notifBadge');
        const list = document.getElementById('notifList');
        const readAllBtn = document.getElementById('notifReadAllBtn');

        if (!wrapper || !toggleBtn || !dropdown || !badge || !list) return;

        const STORAGE_KEY = 'sp_read_notifications';
        let items = [];

        function getReadKeys() {
            try {
                return JSON.parse(localStorage.getItem(STORAGE_KEY) || '[]');
            } catch {
                return [];
            }
        }

        function saveReadKeys(keys) {
            localStorage.setItem(STORAGE_KEY, JSON.stringify(keys));
        }

        function render() {
            const readKeys = new Set(getReadKeys());
            const unreadCount = items.filter((item) => !readKeys.has(item.key)).length;

            if (unreadCount > 0) {
                badge.textContent = unreadCount > 9 ? '9+' : String(unreadCount);
                badge.hidden = false;
            } else {
                badge.hidden = true;
            }

            list.innerHTML = '';

            if (items.length === 0) {
                const empty = document.createElement('div');
                empty.className = 'notif-empty';
                empty.textContent = 'Всё спокойно! Срочных дедлайнов нет.';
                list.appendChild(empty);
                return;
            }

            items.forEach((item) => {
                const a = document.createElement('a');
                a.href = item.url;
                a.className = 'notif-item' + (readKeys.has(item.key) ? ' is-read' : '');

                const tag = document.createElement('span');
                tag.className = 'notif-item-tag is-' + item.status;
                tag.textContent = `${item.label} • ${item.deadline}`;

                const title = document.createElement('span');
                title.className = 'notif-item-title';
                title.textContent = item.title;

                a.appendChild(tag);
                a.appendChild(title);

                a.addEventListener('click', () => {
                    const updated = Array.from(new Set([...getReadKeys(), item.key]));
                    saveReadKeys(updated);
                });

                list.appendChild(a);
            });
        }

        async function loadNotifications() {
            try {
                const res = await fetch('/notifications', {
                    headers: { 'Accept': 'application/json' },
                });
                if (!res.ok) return;
                const data = await res.json();
                items = Array.isArray(data.notifications) ? data.notifications : [];
                render();
            } catch {
                // Игнорируем сетевые ошибки в фоне
            }
        }

        toggleBtn.addEventListener('click', (e) => {
            e.stopPropagation();
            const willOpen = dropdown.hidden;
            dropdown.hidden = !willOpen;
            toggleBtn.setAttribute('aria-expanded', String(willOpen));
        });

        readAllBtn?.addEventListener('click', () => {
            const allKeys = items.map((item) => item.key);
            const merged = Array.from(new Set([...getReadKeys(), ...allKeys]));
            saveReadKeys(merged);
            render();
        });

        document.addEventListener('click', (e) => {
            if (!wrapper.contains(e.target)) {
                dropdown.hidden = true;
                toggleBtn.setAttribute('aria-expanded', 'false');
            }
        });

        loadNotifications();
    })();
</script>
@endif
