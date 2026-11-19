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
    // 2. БЛОК "ДОМАШНЕЕ ЗАДАНИЕ" (синхронизирован с /homework)
    // ==========================================
    const HW_STORAGE_KEY = 'studentplanner_homework';
    const hwInput = document.querySelector('#hwInput');
    const hwContainer = document.querySelector('#hwContainer');

    function loadHomeworkList() {
        const raw = localStorage.getItem(HW_STORAGE_KEY);
        if (raw) {
            try { return JSON.parse(raw); } catch (e) { /* ignore */ }
        }
        return [
            { id: 1, subject: 'Математика',  text: 'Страница 83 учить правило, распечатать правило', deadline: '15.09.25', added: Date.now() - 200000, completed: false },
            { id: 2, subject: 'Информатика', text: 'Лабораторная работа №3: спроектировать реляционную базу данных', deadline: '18.09.25', added: Date.now() - 100000, completed: false },
            { id: 3, subject: 'Физика',      text: 'Задачи 4.12 — 4.18 в сборнике задач', deadline: '20.09.25', added: Date.now(), completed: false }
        ];
    }

    function saveHomeworkList(list) {
        localStorage.setItem(HW_STORAGE_KEY, JSON.stringify(list));
    }

    function renderMainHomework() {
        if (!hwContainer) return;
        const list = loadHomeworkList();

        hwContainer.innerHTML = '';

        if (!list || list.length === 0) {
            hwContainer.innerHTML = '<p class="empty-state" style="color: #9AA0C3; font-size: 1.6rem; text-align: center; margin-top: 3rem;">Домашних заданий пока нет</p>';
            return;
        }

        list.forEach(hw => {
            const hwDiv = document.createElement('div');
            hwDiv.className = 'card-body-tasks-task';
            hwDiv.dataset.id = hw.id;

            const isChecked = hw.completed ? 'checked' : '';
            const hasDeadline = hw.deadline ? 'has-deadline' : '';
            const deadlineHtml = hw.deadline ? `<p>до ${hw.deadline}</p>` : '';
            const titleFull = (hw.subject ? hw.subject + ': ' : '') + hw.text;

            hwDiv.innerHTML = `
                <input type="checkbox" id="main_hw_${hw.id}" ${isChecked}>
                <div class="task-text-content ${hasDeadline}">
                    <label for="main_hw_${hw.id}" title="${titleFull}">
                        <strong>${hw.subject ? hw.subject + ': ' : ''}</strong>${hw.text}
                    </label>
                    ${deadlineHtml}
                </div>
            `;
            hwContainer.appendChild(hwDiv);
        });
    }

    if (hwContainer) {
        renderMainHomework();

        // Отметка выполнения (чекбокс синхронизируется в localStorage)
        hwContainer.addEventListener('change', function (e) {
            if (e.target.matches('input[type="checkbox"]')) {
                const taskCard = e.target.closest('.card-body-tasks-task');
                if (!taskCard) return;
                const id = Number(taskCard.dataset.id);
                const list = loadHomeworkList();
                const item = list.find(h => h.id === id);
                if (item) {
                    item.completed = e.target.checked;
                    saveHomeworkList(list);
                }
            }
        });
    }

    // Быстрое добавление задания с главной страницы через Enter
    if (hwInput) {
        hwInput.addEventListener('keydown', function (e) {
            if (e.key === 'Enter') {
                const fullText = hwInput.value.trim();
                if (fullText === '') return;

                // Если пользователь ввёл "Химия: параграф 12", отделяем предмет
                let subject = 'Задание';
                let text = fullText;
                if (fullText.includes(':')) {
                    const parts = fullText.split(':');
                    subject = parts[0].trim();
                    text = parts.slice(1).join(':').trim();
                }

                const list = loadHomeworkList();
                const newId = list.length > 0 ? Math.max(...list.map(h => h.id)) + 1 : 1;

                list.unshift({
                    id: newId,
                    subject: subject,
                    text: text,
                    deadline: null,
                    added: Date.now(),
                    completed: false
                });

                saveHomeworkList(list);
                renderMainHomework();
                hwInput.value = '';
            }
        });
    }


    // ==========================================
    // 3. БЛОК "РАСПИСАНИЕ"
    // ==========================================
    const prevDayBtn = document.querySelector('#prevDayBtn');
    const nextDayBtn = document.querySelector('#nextDayBtn');
    const currentDayText = document.querySelector('#currentDayText');

    const daysList = [
        '19 ноября, 2025 год (Среда)',
        '20 ноября, 2025 год (Четверг)',
        '21 ноября, 2025 год (Пятница)'
    ];
    let currentDayIndex = 1;

    if (prevDayBtn && nextDayBtn && currentDayText) {
        prevDayBtn.addEventListener('click', function () {
            if (currentDayIndex > 0) {
                currentDayIndex--;
                currentDayText.textContent = daysList[currentDayIndex];
            }
        });

        nextDayBtn.addEventListener('click', function () {
            if (currentDayIndex < daysList.length - 1) {
                currentDayIndex++;
                currentDayText.textContent = daysList[currentDayIndex];
            }
        });
    }


    // ==========================================
    // 4. БЛОК "ЗАМЕТКИ"
    // ==========================================
    const noteInput = document.querySelector('#noteInput');
    const notesContainer = document.querySelector('#notesContainer');

    if (noteInput && notesContainer) {
        noteInput.addEventListener('keydown', function (e) {
            if (e.key === 'Enter') {
                const text = noteInput.value.trim();
                if (text === '') return;

                hideEmptyState(notesContainer);

                const noteDiv = document.createElement('div');
                noteDiv.className = 'card-body-notes-note';
                noteDiv.innerHTML = `
                    <div class="title">Новая заметка</div>
                    <div class="main-text">${text}</div>
                `;

                notesContainer.prepend(noteDiv);
                noteInput.value = '';
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
    const filesContainer = document.querySelector('#filesContainer');

    if (addFileBtn && filesContainer) {
        addFileBtn.addEventListener('click', function () {
            const fileName = prompt('Введите название файла (например: Презентация.pptx):');
            if (fileName) {
                hideEmptyState(filesContainer);

                const fileDiv = document.createElement('div');
                fileDiv.className = 'onefile';
                fileDiv.innerHTML = `
                    <img class="icons" src="/media/file.svg" alt="file">
                    <p>${fileName}</p>
                    <img class="icons" src="/media/dots.svg" alt="dots" style="cursor: pointer;">
                `;
                filesContainer.prepend(fileDiv);
            }
        });
    }

});
