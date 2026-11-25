import { confirmDelete } from './confirmModal';

document.addEventListener('DOMContentLoaded', function () {
    const SORT_KEY = 'studentplanner_hw_sort';

    const homeworkList = document.querySelector('#homeworkList');
    const sortBtns = document.querySelectorAll('.hw-sort-btn');
    const searchInput = document.querySelector('#hwSearchInput');
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';

    if (!homeworkList) return;

    let allHomeworks = [];
    try {
        allHomeworks = JSON.parse(homeworkList.dataset.homeworks || '[]') || [];
    } catch {
        allHomeworks = [];
    }

    let currentSort = localStorage.getItem(SORT_KEY) || 'deadline';

    function parseDeadline(str) {
        if (!str) return new Date(9999, 11, 31);
        const parts = str.split('.');
        if (parts.length !== 3) return new Date(9999, 11, 31);
        const [d, m, y] = parts;
        const year = y.length === 2 ? '20' + y : y;
        return new Date(`${year}-${m}-${d}`);
    }

    function sortHomework(list) {
        if (currentSort === 'deadline') {
            return [...list].sort((a, b) => parseDeadline(a.deadline) - parseDeadline(b.deadline));
        }
        if (currentSort === 'added') {
            return [...list].sort((a, b) => (b.added || 0) - (a.added || 0));
        }
        return list;
    }

    function filterHomework(list) {
        const query = searchInput ? searchInput.value.trim().toLowerCase() : '';
        if (!query) return list;
        return list.filter(
            (hw) =>
                hw.subject.toLowerCase().includes(query) ||
                hw.text.toLowerCase().includes(query)
        );
    }

    function renderHomework() {
        const sorted = sortHomework(filterHomework(allHomeworks));
        homeworkList.innerHTML = '';

        if (sorted.length === 0) {
            const q = searchInput ? searchInput.value.trim() : '';
            const empty = document.createElement('div');
            empty.className = 'empty-state';
            empty.textContent = q
                ? `Ничего не найдено по запросу «${q}»`
                : 'У вас пока нет домашних заданий.';
            homeworkList.appendChild(empty);
            return;
        }

        sorted.forEach((hw) => {
            const hasFile = Boolean(hw.fileName && hw.downloadUrl);
            const deadlineTxt = hw.deadline ? `до ${hw.deadline}` : '';

            const card = document.createElement('div');
            card.className = 'one-homework';
            card.dataset.id = hw.id;

            const info = document.createElement('div');
            info.className = 'homework-info';

            const nameEl = document.createElement('div');
            nameEl.className = 'homework-name';
            nameEl.textContent = hw.subject;
            info.appendChild(nameEl);

            const textEl = document.createElement('div');
            textEl.className = 'homework-text';
            textEl.textContent = hw.text;
            info.appendChild(textEl);

            if (hasFile) {
                const fileEl = document.createElement('div');
                fileEl.className = 'hw-attached-file';
                fileEl.textContent = `Файл: ${hw.fileName}`;
                info.appendChild(fileEl);
            }

            const meta = document.createElement('div');
            meta.className = 'homework-meta';

            if (deadlineTxt) {
                const dlEl = document.createElement('span');
                dlEl.className = 'homework-deadline';
                dlEl.textContent = deadlineTxt;
                meta.appendChild(dlEl);
            }

            const buttons = document.createElement('div');
            buttons.className = 'homework-buttons';

            if (hasFile) {
                const dlLink = document.createElement('a');
                dlLink.href = hw.downloadUrl;
                dlLink.className = 'hw-btn hw-btn-download';
                dlLink.title = 'Скачать прикреплённый файл';
                dlLink.innerHTML = '<img src="/media/download.svg" alt="download">';
                buttons.appendChild(dlLink);
            }

            const delBtn = document.createElement('button');
            delBtn.type = 'button';
            delBtn.className = 'hw-btn hw-btn-delete';
            delBtn.title = 'Удалить задание';
            delBtn.innerHTML = '<img src="/media/delete.svg" alt="delete">';
            buttons.appendChild(delBtn);

            meta.appendChild(buttons);
            card.appendChild(info);
            card.appendChild(meta);
            homeworkList.appendChild(card);
        });
    }

    homeworkList.addEventListener('click', async function (event) {
        const deleteBtn = event.target.closest('.hw-btn-delete');
        if (!deleteBtn) return;

        const card = deleteBtn.closest('.one-homework');
        if (!card) return;
        const id = Number(card.dataset.id);
        const hw = allHomeworks.find((h) => h.id === id);
        if (!hw) return;

        const confirmed = await confirmDelete({
            title: 'Удалить задание?',
            message: `Вы уверены, что хотите удалить домашнее задание по предмету «${hw.subject}»?`,
        });
        if (!confirmed) return;

        try {
            const res = await fetch(hw.deleteUrl, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json',
                },
            });
            if (!res.ok) return;

            allHomeworks = allHomeworks.filter((item) => item.id !== id);
            renderHomework();
        } catch {
            // ignore
        }
    });

    sortBtns.forEach((btn) => {
        btn.classList.toggle('active', btn.dataset.sort === currentSort);

        btn.addEventListener('click', function () {
            currentSort = this.dataset.sort;
            localStorage.setItem(SORT_KEY, currentSort);
            sortBtns.forEach((b) => b.classList.remove('active'));
            this.classList.add('active');
            renderHomework();
        });
    });

    if (searchInput) {
        searchInput.addEventListener('input', renderHomework);
    }

    renderHomework();
});
