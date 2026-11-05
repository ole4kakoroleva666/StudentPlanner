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
    // 2. БЛОК "ДОМАШНЕЕ ЗАДАНИЕ"
    // ==========================================
    const hwInput = document.querySelector('#hwInput');
    const hwContainer = document.querySelector('#hwContainer');

    if (hwInput && hwContainer) {
        hwInput.addEventListener('keydown', function (e) {
            if (e.key === 'Enter') {
                const text = hwInput.value.trim();
                if (text === '') return;

                hideEmptyState(hwContainer);

                const newId = 'hw_' + Date.now();
                const hwDiv = document.createElement('div');
                hwDiv.className = 'card-body-tasks-task';
                hwDiv.innerHTML = `
                    <input type="checkbox" id="${newId}">
                    <label for="${newId}">${text}</label>
                `;

                hwContainer.prepend(hwDiv);
                hwInput.value = '';
            }
        });

        hwContainer.addEventListener('change', function (e) {
            if (e.target.matches('input[type="checkbox"]')) {
                const label = e.target.closest('.card-body-tasks-task').querySelector('label');
                if (label) {
                    label.style.textDecoration = e.target.checked ? 'line-through' : 'none';
                    label.style.opacity = e.target.checked ? '0.5' : '1';
                }
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
    // 5. БЛОК "КАЛЕНДАРЬ"
    // ==========================================
    const daysElements = document.querySelectorAll('#miniCalendarDays .day');
    daysElements.forEach(function (dayElem) {
        dayElem.style.cursor = 'pointer';
        dayElem.addEventListener('click', function () {
            daysElements.forEach(d => {
                d.style.background = 'transparent';
                d.style.color = 'inherit';
            });
            dayElem.style.background = '#3874FF';
            dayElem.style.color = '#fff';
            dayElem.style.borderRadius = '6px';
        });
    });


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
