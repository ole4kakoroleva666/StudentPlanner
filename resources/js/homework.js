document.addEventListener('DOMContentLoaded', function () {

    const STORAGE_KEY = 'studentplanner_homework';
    const SORT_KEY    = 'studentplanner_hw_sort';

    const homeworkList = document.querySelector('#homeworkList');
    const sortBtns     = document.querySelectorAll('.hw-sort-btn');
    const searchInput  = document.querySelector('#hwSearchInput');

    if (!homeworkList) return;

    // ========================================================
    // ДАННЫЕ: читаем / пишем из localStorage
    // ========================================================
    function loadHomework() {
        const raw = localStorage.getItem(STORAGE_KEY);
        return raw ? JSON.parse(raw) : getDefaultHomework();
    }

    function saveHomework(list) {
        localStorage.setItem(STORAGE_KEY, JSON.stringify(list));
    }

    function getDefaultHomework() {
        const defaults = [
            { id: 1, subject: 'Математика',  text: 'Страница 83 учить правило, распечатать правило', deadline: '15.09.25', added: Date.now() - 200000, fileName: null, fileData: null },
            { id: 2, subject: 'Информатика', text: 'Лабораторная работа №3: спроектировать реляционную базу данных', deadline: '18.09.25', added: Date.now() - 100000, fileName: null, fileData: null },
            { id: 3, subject: 'Физика',      text: 'Задачи 4.12 — 4.18 в сборнике задач', deadline: '20.09.25', added: Date.now(), fileName: null, fileData: null },
        ];
        saveHomework(defaults);
        return defaults;
    }

    // ========================================================
    // СОРТИРОВКА: По дедлайну / По добавлению
    // ========================================================
    let currentSort = localStorage.getItem(SORT_KEY) || 'deadline';

    function parseDeadline(str) {
        if (!str) return new Date(9999, 11, 31); // без дедлайна — в конец
        const parts = str.split('.');
        if (parts.length !== 3) return new Date(9999, 11, 31);
        const [d, m, y] = parts;
        const year = y.length === 2 ? '20' + y : y;
        return new Date(`${year}-${m}-${d}`);
    }

    function sortHomework(list) {
        if (currentSort === 'deadline') {
            // Ближайшие сроки выше
            return [...list].sort((a, b) => parseDeadline(a.deadline) - parseDeadline(b.deadline));
        } else if (currentSort === 'added') {
            // Новые добавленные выше
            return [...list].sort((a, b) => (b.added || 0) - (a.added || 0));
        }
        return list;
    }

    // ========================================================
    // ПОИСК
    // ========================================================
    function filterHomework(list) {
        const query = searchInput ? searchInput.value.trim().toLowerCase() : '';
        if (!query) return list;
        return list.filter(hw =>
            hw.subject.toLowerCase().includes(query) ||
            hw.text.toLowerCase().includes(query)
        );
    }

    // ========================================================
    // ОТРИСОВКА КАРТОЧЕК
    // ========================================================
    function renderHomework() {
        const all    = loadHomework();
        const sorted = sortHomework(filterHomework(all));

        homeworkList.innerHTML = '';

        if (sorted.length === 0) {
            const q = searchInput ? searchInput.value.trim() : '';
            homeworkList.innerHTML = `
                <div class="empty-state">
                    ${q ? 'Ничего не найдено по запросу «' + q + '»' : '🎉 Все домашние задания выполнены!'}
                </div>
            `;
            return;
        }

        sorted.forEach(hw => {
            const hasFile     = hw.fileName && hw.fileData;
            const deadlineTxt = hw.deadline ? `до ${hw.deadline}` : '';

            const card = document.createElement('div');
            card.className = 'one-homework';
            card.dataset.id = hw.id;
            card.innerHTML = `
                <div class="homework-info">
                    <div class="homework-name">${hw.subject}</div>
                    <div class="homework-text">${hw.text}</div>
                    ${hasFile ? `<div class="hw-attached-file">📎 ${hw.fileName}</div>` : ''}
                </div>
                <div class="homework-meta">
                    ${deadlineTxt ? `<span class="homework-deadline">${deadlineTxt}</span>` : ''}
                    <div class="homework-buttons">
                        ${hasFile ? `
                        <button type="button" class="hw-btn hw-btn-download" title="Скачать прикреплённый файл">
                            <img src="/media/download.svg" alt="download">
                        </button>` : ''}
                        <button type="button" class="hw-btn hw-btn-delete" title="Удалить задание">
                            <img src="/media/delete.svg" alt="delete">
                        </button>
                    </div>
                </div>
            `;
            homeworkList.appendChild(card);
        });
    }

    // ========================================================
    // КЛИКИ: удаление (с подтверждением) и скачивание файла
    // ========================================================
    homeworkList.addEventListener('click', function (event) {
        // --- 1. Удаление с предупреждением ---
        const deleteBtn = event.target.closest('.hw-btn-delete');
        if (deleteBtn) {
            const card = deleteBtn.closest('.one-homework');
            if (!card) return;
            const subjectName = card.querySelector('.homework-name')?.textContent || 'это задание';

            // Всплывающее предупреждение перед удалением:
            const confirmed = confirm(`Вы уверены, что хотите удалить домашнее задание по предмету «${subjectName}»?`);
            if (!confirmed) return;

            const id = Number(card.dataset.id);
            let list = loadHomework().filter(hw => hw.id !== id);
            saveHomework(list);
            renderHomework();
            return;
        }

        // --- 2. Скачивание прикреплённого файла ---
        const downloadBtn = event.target.closest('.hw-btn-download');
        if (downloadBtn) {
            const card = downloadBtn.closest('.one-homework');
            if (!card) return;
            const id = Number(card.dataset.id);
            const hw = loadHomework().find(h => h.id === id);
            if (hw && hw.fileData && hw.fileName) {
                const link = document.createElement('a');
                link.href     = hw.fileData;
                link.download = hw.fileName;
                document.body.appendChild(link);
                link.click();
                document.body.removeChild(link);
            }
        }
    });

    // ========================================================
    // ПЕРЕКЛЮЧЕНИЕ СОРТИРОВКИ
    // ========================================================
    sortBtns.forEach(btn => {
        if (btn.dataset.sort === currentSort) {
            btn.classList.add('active');
        } else {
            btn.classList.remove('active');
        }

        btn.addEventListener('click', function () {
            currentSort = this.dataset.sort;
            localStorage.setItem(SORT_KEY, currentSort);

            sortBtns.forEach(b => b.classList.remove('active'));
            this.classList.add('active');

            renderHomework();
        });
    });

    // ========================================================
    // ПОИСК В РЕАЛЬНОМ ВРЕМЕНИ
    // ========================================================
    if (searchInput) {
        searchInput.addEventListener('input', renderHomework);
    }

    // ========================================================
    // СТАРТ
    // ========================================================
    renderHomework();
});
