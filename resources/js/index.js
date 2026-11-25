import { confirmDelete } from './confirmModal';

document.addEventListener('DOMContentLoaded', function () {

    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

    // Вспомогательная функция для скрытия заглушки "У вас пока нет..." при добавлении элемента
    function hideEmptyState(container) {
        const empty = container.querySelector('.empty-state');
        if (empty) empty.remove();
    }

    // ==========================================
    // 1. БЛОК "ЗАДАЧИ" (с сохранением в БД!)
    // ==========================================
    const taskInput = document.querySelector('#taskInput');
    const addTaskBtn = document.querySelector('#addTaskBtn');
    const tasksContainer = document.querySelector('#tasksContainer');

    function addNewTask() {
        if (!taskInput || !tasksContainer) return;
        const text = taskInput.value.trim();
        if (text === '') return;

        // Отправляем запрос на сервер в Laravel
        fetch('/tasks', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json'
            },
            body: JSON.stringify({ task_text: text })
        })
        .then(response => {
            if (!response.ok) throw new Error('Ошибка сервера');
            return response.json();
        })
        .then(data => {
            if (data.success) {
                hideEmptyState(tasksContainer);

                const task = data.task;
                const taskDiv = document.createElement('div');
                taskDiv.className = 'card-body-tasks-task';
                taskDiv.dataset.id = task.id;
                const hasDeadlineClass = task.deadline_formatted ? 'has-deadline' : '';
                const deadlineHtml = task.deadline_formatted ? `<p>до ${task.deadline_formatted}</p>` : '';
                taskDiv.innerHTML = `
                    <input type="checkbox" id="task_${task.id}">
                    <div class="task-text-content ${hasDeadlineClass}">
                        <label for="task_${task.id}" title="${task.title}">${task.title}</label>
                        ${deadlineHtml}
                    </div>
                `;

                tasksContainer.prepend(taskDiv);
                taskInput.value = '';
            }
        })
        .catch(err => {
            console.error('Не удалось сохранить задачу:', err);
        });
    }

    if (addTaskBtn) addTaskBtn.addEventListener('click', addNewTask);
    if (taskInput) {
        taskInput.addEventListener('keydown', function (e) {
            if (e.key === 'Enter') addNewTask();
        });
    }

    // Переключение галочки с сохранением в БД!
    if (tasksContainer) {
        tasksContainer.addEventListener('change', function (e) {
            if (e.target.matches('input[type="checkbox"]')) {
                const taskDiv = e.target.closest('.card-body-tasks-task');
                const taskId = taskDiv ? taskDiv.dataset.id : null;

                if (taskId) {
                    fetch(`/tasks/${taskId}/toggle`, {
                        method: 'PATCH',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': csrfToken,
                            'Accept': 'application/json'
                        }
                    }).catch(err => console.error('Ошибка обновления статуса:', err));
                }
            }
        });
    }


    // ==========================================
    // 2. БЛОК "ДОМАШНЕЕ ЗАДАНИЕ" (из БД)
    // ==========================================
    const hwInput = document.querySelector('#hwInput');
    const hwContainer = document.querySelector('#hwContainer');

    let homeHomeworks = [];
    if (hwContainer) {
        try {
            homeHomeworks = JSON.parse(hwContainer.dataset.homeworks || '[]') || [];
        } catch {
            homeHomeworks = [];
        }
    }

    function escapeHtml(str) {
        return String(str)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;');
    }

    function renderMainHomework() {
        if (!hwContainer) return;
        hwContainer.innerHTML = '';

        if (!homeHomeworks || homeHomeworks.length === 0) {
            hwContainer.innerHTML = '<p class="empty-state" style="color: #9AA0C3; font-size: 1.6rem; text-align: center; margin-top: 3rem;">Домашних заданий пока нет</p>';
            return;
        }

        homeHomeworks.forEach(hw => {
            const hwDiv = document.createElement('div');
            hwDiv.className = 'card-body-tasks-task';
            hwDiv.dataset.id = hw.id;

            const hasDeadline = hw.deadline ? 'has-deadline' : '';
            const deadlineHtml = hw.deadline ? `<p>до ${escapeHtml(hw.deadline)}</p>` : '';
            const titleFull = (hw.subject ? hw.subject + ': ' : '') + hw.text;

            hwDiv.innerHTML = `
                <input type="checkbox" id="main_hw_${hw.id}">
                <div class="task-text-content ${hasDeadline}">
                    <label for="main_hw_${hw.id}" title="${escapeHtml(titleFull)}">
                        <strong>${hw.subject ? escapeHtml(hw.subject) + ': ' : ''}</strong>${escapeHtml(hw.text)}
                    </label>
                    ${deadlineHtml}
                </div>
            `;
            hwContainer.appendChild(hwDiv);
        });
    }

    if (hwContainer) {
        renderMainHomework();
    }

    // Быстрое добавление ДЗ с главной страницы через Enter (в БД)
    if (hwInput && hwContainer) {
        hwInput.addEventListener('keydown', async function (e) {
            if (e.key !== 'Enter') return;
            const fullText = hwInput.value.trim();
            if (fullText === '') return;

            let subject = 'Задание';
            let text = fullText;
            if (fullText.includes(':')) {
                const parts = fullText.split(':');
                subject = parts[0].trim() || 'Задание';
                text = parts.slice(1).join(':').trim() || fullText;
            }

            const storeUrl = hwContainer.dataset.storeUrl || '/homework';
            hwInput.value = '';

            try {
                const res = await fetch(storeUrl, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify({
                        subject,
                        description: text,
                    }),
                });
                if (!res.ok) return;
                const data = await res.json();
                if (data.homework) {
                    homeHomeworks.unshift(data.homework);
                    renderMainHomework();
                }
            } catch {
                // ignore
            }
        });
    }


    // ==========================================
    // 3. БЛОК "РАСПИСАНИЕ" (моковые данные)
    // ==========================================
    const prevDayBtn = document.querySelector('#prevDayBtn');
    const nextDayBtn = document.querySelector('#nextDayBtn');
    const currentDayText = document.querySelector('#currentDayText');
    const homeScheduleList = document.querySelector('#homeScheduleList');

    const scheduleDays = [
        {
            title: '16 ноября, 2026 год (Понедельник)',
            lessons: [
                { time: '14:15 — 15:50', subject: 'ПИРВП', classroom: '1-306', teacher: 'Евдокимов' },
                { time: '16:10 — 17:45', subject: 'ПИРВП', classroom: '1-306', teacher: 'Евдокимов' },
                { time: '17:55 — 19:30', subject: 'Стандартизация', classroom: '2-204', teacher: 'Сухорукова' }
            ]
        },
        {
            title: '17 ноября, 2026 год (Вторник)',
            lessons: [
                { time: '10:15 — 11:50', subject: 'ОВБП', classroom: '3-102', teacher: 'Бушуева' },
                { time: '12:30 — 14:05', subject: 'ОВБП', classroom: '3-102', teacher: 'Бушуева' },
                { time: '14:15 — 15:50', subject: 'Стандартизация', classroom: '2-204', teacher: 'Сухорукова' }
            ]
        },
        {
            title: '18 ноября, 2026 год (Среда)',
            lessons: [
                { time: '08:30 — 10:05', subject: 'Стандартизация', classroom: '2-204', teacher: 'Сухорукова' },
                { time: '10:15 — 11:50', subject: 'ПИРВП', classroom: '1-306', teacher: 'Евдокимов' },
                { time: '12:30 — 14:05', subject: 'ПИРВП', classroom: '1-306', teacher: 'Евдокимов' }
            ]
        },
        {
            title: '19 ноября, 2026 год (Четверг)',
            lessons: [
                { time: '12:30 — 14:05', subject: 'ОВБП', classroom: '3-102', teacher: 'Бушуева' },
                { time: '14:15 — 15:50', subject: 'Стандартизация', classroom: '2-204', teacher: 'Сухорукова' },
                { time: '16:10 — 17:45', subject: 'ПИРВП', classroom: '1-306', teacher: 'Евдокимов' }
            ]
        },
        {
            title: '20 ноября, 2026 год (Пятница)',
            lessons: [
                { time: '10:15 — 11:50', subject: 'ПИРВП', classroom: '1-306', teacher: 'Евдокимов' },
                { time: '12:30 — 14:05', subject: 'ОВБП', classroom: '3-102', teacher: 'Бушуева' },
                { time: '14:15 — 15:50', subject: 'ОВБП', classroom: '3-102', teacher: 'Бушуева' }
            ]
        }
    ];
    let currentDayIndex = 2;

    function renderHomeSchedule(index) {
        const dayData = scheduleDays[index];
        if (!dayData || !currentDayText || !homeScheduleList) return;

        currentDayText.textContent = dayData.title;

        const timeIcon = homeScheduleList.dataset.timeIcon || '/media/time.svg';
        const booksIcon = homeScheduleList.dataset.booksIcon || '/media/books.svg';
        const classroomIcon = homeScheduleList.dataset.classroomIcon || '/media/chool.svg';
        const teacherIcon = homeScheduleList.dataset.teacherIcon || '/media/teacher.svg';

        homeScheduleList.innerHTML = dayData.lessons.map(lesson => `
            <div class="shedule">
                <div class="time">
                    <img class="icons" src="${timeIcon}" alt="time">
                    <p>Время: ${lesson.time}</p>
                </div>
                <div class="subject">
                    <img class="icons" src="${booksIcon}" alt="subject">
                    <p>Предмет: ${lesson.subject}</p>
                </div>
                <div class="classroom">
                    <img class="icons" src="${classroomIcon}" alt="classroom">
                    <p>Аудитория: ${lesson.classroom}</p>
                </div>
                <div class="teacher">
                    <img class="icons" src="${teacherIcon}" alt="teacher">
                    <p>Преподаватель: ${lesson.teacher}</p>
                </div>
            </div>
        `).join('');
    }

    if (prevDayBtn && nextDayBtn && currentDayText && homeScheduleList) {
        renderHomeSchedule(currentDayIndex);

        prevDayBtn.addEventListener('click', function () {
            if (currentDayIndex > 0) {
                currentDayIndex--;
                renderHomeSchedule(currentDayIndex);
            }
        });

        nextDayBtn.addEventListener('click', function () {
            if (currentDayIndex < scheduleDays.length - 1) {
                currentDayIndex++;
                renderHomeSchedule(currentDayIndex);
            }
        });
    }


    // ==========================================
    // 4. БЛОК "ЗАМЕТКИ" (с сохранением в БД!)
    // ==========================================
    const noteInput = document.querySelector('#noteInput');
    const notesContainer = document.querySelector('#notesContainer');

    function escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }

    if (noteInput && notesContainer) {
        noteInput.addEventListener('keydown', function (e) {
            if (e.key === 'Enter') {
                const text = noteInput.value.trim();
                if (text === '') return;

                fetch('/notes', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        title: text
                    })
                })
                .then(response => {
                    if (!response.ok) throw new Error('Ошибка сохранения заметки');
                    return response.json();
                })
                .then(data => {
                    if (data.success && data.note) {
                        hideEmptyState(notesContainer);

                        const note = data.note;
                        const noteLink = document.createElement('a');
                        noteLink.href = '/notes';
                        noteLink.className = 'card-body-notes-note';

                        const titleText = note.title || 'Без названия';
                        const tmpDiv = document.createElement('div');
                        tmpDiv.innerHTML = note.content || '';
                        const plain = (tmpDiv.textContent || tmpDiv.innerText || '').trim() || 'Пустая заметка';

                        noteLink.innerHTML = `
                            <h3>${escapeHtml(titleText)}</h3>
                            <p>${escapeHtml(plain.length > 70 ? plain.substring(0, 70) + '...' : plain)}</p>
                        `;

                        notesContainer.prepend(noteLink);
                        noteInput.value = '';
                    }
                })
                .catch(err => {
                    console.error('Ошибка сохранения заметки:', err);
                });
            }
        });
    }


    // ==========================================
    // 5. БЛОК "КАЛЕНДАРЬ" (С выбором месяца и года)
    // ==========================================
    const miniCalendarDays = document.querySelector('#miniCalendarDays');
    const miniCalMonthSelect = document.querySelector('#miniCalMonthSelect');
    const miniCalYearSelect = document.querySelector('#miniCalYearSelect');
    const miniCalPrevBtn = document.querySelector('#miniCalPrevBtn');
    const miniCalNextBtn = document.querySelector('#miniCalNextBtn');

    if (miniCalendarDays && miniCalMonthSelect && miniCalYearSelect) {
        const monthNames = [
            'Январь', 'Февраль', 'Март', 'Апрель', 'Май', 'Июнь',
            'Июль', 'Август', 'Сентябрь', 'Октябрь', 'Ноябрь', 'Декабрь'
        ];

        let displayDate = new Date();

        // Заполняем месяцы в <select>
        monthNames.forEach(function (name, index) {
            const option = document.createElement('option');
            option.value = index;
            option.textContent = name;
            miniCalMonthSelect.appendChild(option);
        });

        // Заполняем года в <select> с широким диапазоном (например, ±10 лет),
        // чтобы появился удобный скролл ("листалка")
        const currentYear = new Date().getFullYear();
        const startYear = currentYear - 10;
        const endYear = currentYear + 10;
        for (let y = startYear; y <= endYear; y++) {
            const option = document.createElement('option');
            option.value = y;
            option.textContent = y;
            miniCalYearSelect.appendChild(option);
        }

        function renderMiniCalendar() {
            const year = displayDate.getFullYear();
            const month = displayDate.getMonth();

            // Если пролистали дальше диапазона — добавляем год динамически
            if (!miniCalYearSelect.querySelector(`option[value="${year}"]`)) {
                const opt = document.createElement('option');
                opt.value = year;
                opt.textContent = year;
                if (year < startYear) {
                    miniCalYearSelect.prepend(opt);
                } else {
                    miniCalYearSelect.appendChild(opt);
                }
            }

            // Синхронизируем значения в select
            miniCalMonthSelect.value = month;
            miniCalYearSelect.value = year;

            miniCalendarDays.innerHTML = '';

            const firstDayIndex = new Date(year, month, 1).getDay();
            const daysInMonth = new Date(year, month + 1, 0).getDate();
            const prevMonthLastDay = new Date(year, month, 0).getDate();

            // Сдвиг для понедельника: вс (0) -> 6, пн (1) -> 0 и т.д.
            const shift = (firstDayIndex === 0) ? 6 : firstDayIndex - 1;

            const now = new Date();
            const isCurrentMonth = (now.getFullYear() === year && now.getMonth() === month);
            const todayDate = now.getDate();

            // 1. Дни предыдущего месяца
            for (let i = shift; i > 0; i--) {
                const dayNum = prevMonthLastDay - i + 1;
                const dayDiv = document.createElement('div');
                dayDiv.className = 'day day-prev';
                dayDiv.textContent = dayNum < 10 ? '0' + dayNum : dayNum;

                const prevDate = new Date(year, month - 1, dayNum);
                const pMonth = String(prevDate.getMonth() + 1).padStart(2, '0');
                const pDay = String(dayNum).padStart(2, '0');
                const pDateStr = `${prevDate.getFullYear()}-${pMonth}-${pDay}`;

                dayDiv.style.cursor = 'pointer';
                dayDiv.addEventListener('click', function () {
                    window.location.href = `/calendarOneDay?date=${pDateStr}`;
                });

                miniCalendarDays.appendChild(dayDiv);
            }

            // 2. Дни текущего месяца
            for (let day = 1; day <= daysInMonth; day++) {
                const dayDiv = document.createElement('div');
                dayDiv.className = 'day';
                dayDiv.textContent = day < 10 ? '0' + day : day;

                const formattedMonth = String(month + 1).padStart(2, '0');
                const formattedDay = String(day).padStart(2, '0');
                const fullDateStr = `${year}-${formattedMonth}-${formattedDay}`;

                if (isCurrentMonth && day === todayDate) {
                    dayDiv.classList.add('day-today');
                }

                dayDiv.addEventListener('click', function () {
                    window.location.href = `/calendarOneDay?date=${fullDateStr}`;
                });

                miniCalendarDays.appendChild(dayDiv);
            }

            // 3. Дни следующего месяца: заполняем ровно до 42 ячеек (6 строк по 7 дней),
            // чтобы сетка всегда была идеально ровной и карточка не прыгала по высоте
            const totalRendered = shift + daysInMonth;
            const remainingDays = 42 - totalRendered;
            for (let nextDay = 1; nextDay <= remainingDays; nextDay++) {
                const dayDiv = document.createElement('div');
                dayDiv.className = 'day day-next';
                dayDiv.textContent = nextDay < 10 ? '0' + nextDay : nextDay;

                const nextDate = new Date(year, month + 1, nextDay);
                const nMonth = String(nextDate.getMonth() + 1).padStart(2, '0');
                const nDay = String(nextDay).padStart(2, '0');
                const nDateStr = `${nextDate.getFullYear()}-${nMonth}-${nDay}`;

                dayDiv.style.cursor = 'pointer';
                dayDiv.addEventListener('click', function () {
                    window.location.href = `/calendarOneDay?date=${nDateStr}`;
                });

                miniCalendarDays.appendChild(dayDiv);
            }
        }

        // Выбор месяца из списка
        miniCalMonthSelect.addEventListener('change', function () {
            displayDate.setMonth(parseInt(this.value, 10));
            renderMiniCalendar();
        });

        // Выбор года из списка
        miniCalYearSelect.addEventListener('change', function () {
            displayDate.setFullYear(parseInt(this.value, 10));
            renderMiniCalendar();
        });

        // Стрелка "Предыдущий месяц"
        if (miniCalPrevBtn) {
            miniCalPrevBtn.addEventListener('click', function () {
                displayDate.setMonth(displayDate.getMonth() - 1);
                renderMiniCalendar();
            });
        }

        // Стрелка "Следующий месяц"
        if (miniCalNextBtn) {
            miniCalNextBtn.addEventListener('click', function () {
                displayDate.setMonth(displayDate.getMonth() + 1);
                renderMiniCalendar();
            });
        }

        renderMiniCalendar();
    }


    // ==========================================
    // 6. БЛОК "МОИ ФАЙЛЫ"
    // ==========================================
    const addFileBtn = document.querySelector('#addFileBtn');
    const homeFileInput = document.querySelector('#homeFileInput');
    const filesContainer = document.querySelector('#filesContainer');

    function showFilesEmptyState() {
        if (!filesContainer) return;
        if (filesContainer.querySelector('.onefile') || filesContainer.querySelector('.empty-state')) return;
        const p = document.createElement('p');
        p.className = 'empty-state';
        p.style.cssText = 'color: #9AA0C3; font-size: 1.6rem; text-align: center; width: 100%; margin-top: 2rem;';
        p.textContent = 'Файлы ещё не загружены';
        filesContainer.appendChild(p);
    }

    function createHomeFileCard(file) {
        const fileIcon = filesContainer.dataset.fileIcon || '/media/fiels.svg';
        const downloadIcon = filesContainer.dataset.downloadIcon || '/media/download.svg';
        const deleteIcon = filesContainer.dataset.deleteIcon || '/media/delete.svg';

        const fileDiv = document.createElement('div');
        fileDiv.className = 'onefile';
        fileDiv.dataset.fileId = String(file.id);
        fileDiv.dataset.fileName = file.original_name;

        const iconImg = document.createElement('img');
        iconImg.className = 'icons';
        iconImg.src = fileIcon;
        iconImg.alt = 'file';

        const nameP = document.createElement('p');
        nameP.className = 'onefile-name';
        nameP.title = file.original_name;
        nameP.textContent = file.original_name;

        const actions = document.createElement('div');
        actions.className = 'onefile-actions';

        const downloadLink = document.createElement('a');
        downloadLink.href = file.download_url;
        downloadLink.className = 'onefile-action-btn';
        downloadLink.title = 'Скачать';
        downloadLink.innerHTML = `<img class="icons" src="${downloadIcon}" alt="Скачать">`;

        const deleteBtn = document.createElement('button');
        deleteBtn.type = 'button';
        deleteBtn.className = 'onefile-action-btn onefile-delete-btn';
        deleteBtn.dataset.deleteUrl = file.delete_url;
        deleteBtn.title = 'Удалить';
        deleteBtn.innerHTML = `<img class="icons" src="${deleteIcon}" alt="Удалить">`;

        actions.append(downloadLink, deleteBtn);
        fileDiv.append(iconImg, nameP, actions);
        return fileDiv;
    }

    if (addFileBtn && homeFileInput && filesContainer) {
        const uploadUrl = filesContainer.dataset.uploadUrl || '/privateFiels';

        addFileBtn.addEventListener('click', function () {
            homeFileInput.click();
        });

        homeFileInput.addEventListener('change', async function () {
            const selected = Array.from(homeFileInput.files || []);
            if (selected.length === 0) return;

            addFileBtn.disabled = true;

            for (const file of selected) {
                const formData = new FormData();
                formData.append('file', file);

                try {
                    const response = await fetch(uploadUrl, {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': csrfToken,
                            'Accept': 'application/json',
                        },
                        body: formData,
                    });
                    const data = await response.json().catch(() => ({}));
                    if (response.ok && data.success && data.file) {
                        hideEmptyState(filesContainer);
                        filesContainer.prepend(createHomeFileCard(data.file));
                    }
                } catch (err) {
                    console.error('Ошибка загрузки файла:', err);
                }
            }

            homeFileInput.value = '';
            addFileBtn.disabled = false;
        });

        filesContainer.addEventListener('click', async function (event) {
            const deleteBtn = event.target.closest('.onefile-delete-btn');
            if (!deleteBtn) return;

            const row = deleteBtn.closest('.onefile');
            const deleteUrl = deleteBtn.dataset.deleteUrl;
            const fileName = row?.dataset.fileName || 'этот файл';
            if (!row || !deleteUrl) return;

            const confirmed = await confirmDelete({
                title: 'Удалить файл?',
                message: `Вы уверены, что хотите удалить «${fileName}»?`,
                confirmText: 'Удалить',
                cancelText: 'Отмена',
            });

            if (!confirmed) return;

            deleteBtn.disabled = true;
            try {
                const response = await fetch(deleteUrl, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json',
                    },
                });
                if (response.ok) {
                    row.remove();
                    showFilesEmptyState();
                } else {
                    deleteBtn.disabled = false;
                }
            } catch (err) {
                deleteBtn.disabled = false;
                console.error('Ошибка удаления файла:', err);
            }
        });
    }

});
