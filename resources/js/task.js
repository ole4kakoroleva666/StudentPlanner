document.addEventListener('DOMContentLoaded', function () {
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

    const tasksContainer = document.querySelector('#tasksContainer');
    const addTaskForm = document.querySelector('#addTaskForm');
    const taskTextInput = document.querySelector('#taskTextInput');
    const deadlineDate = document.querySelector('#deadlineDate');
    const deadlineDisplay = document.querySelector('#deadlineDisplay');
    const deadlineIcon = document.querySelector('.deadline-icon-button');

    function escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }

    function hideEmptyState() {
        if (!tasksContainer) return;
        const empty = tasksContainer.querySelector('.empty-state');
        if (empty) empty.remove();
    }

    // 1. Проверка длинного текста и добавление кнопки "Читать далее"
    function setupReadMoreButtons() {
        if (!tasksContainer) return;
        const wrappers = tasksContainer.querySelectorAll('.task-title-wrapper');

        wrappers.forEach(wrapper => {
            if (wrapper.querySelector('.read-more-btn')) return;

            const title = wrapper.querySelector('.task-title');
            if (!title) return;

            // Если реальная высота содержимого больше видимой высоты 2 строк
            if (title.scrollHeight > title.clientHeight + 1) {
                const btn = document.createElement('button');
                btn.type = 'button';
                btn.className = 'read-more-btn';
                btn.textContent = 'Читать далее';

                btn.addEventListener('click', function (e) {
                    e.preventDefault();
                    e.stopPropagation();

                    title.classList.toggle('is-expanded');
                    if (title.classList.contains('is-expanded')) {
                        btn.textContent = 'Свернуть';
                    } else {
                        btn.textContent = 'Читать далее';
                    }
                });

                wrapper.appendChild(btn);
            }
        });
    }

    // Инициализируем кнопки для существующих задач
    setupReadMoreButtons();

    // При изменении размеров окна перепроверяем необходимость кнопок
    window.addEventListener('resize', setupReadMoreButtons);

    // 2. Выбор даты (срок сдачи)
    function openDatePicker() {
        if (!deadlineDate) return;
        if (typeof deadlineDate.showPicker === 'function') {
            deadlineDate.showPicker();
        } else {
            deadlineDate.focus();
            deadlineDate.click();
        }
    }

    if (deadlineDisplay) {
        deadlineDisplay.addEventListener('click', openDatePicker);
    }

    if (deadlineIcon) {
        deadlineIcon.addEventListener('click', function (e) {
            e.preventDefault();
            openDatePicker();
        });
    }

    if (deadlineDate && deadlineDisplay) {
        deadlineDate.addEventListener('change', function () {
            if (this.value) {
                const parts = this.value.split('-'); // YYYY-MM-DD
                if (parts.length === 3) {
                    const day = parts[2];
                    const month = parts[1];
                    const year = parts[0].slice(-2);
                    deadlineDisplay.textContent = `${day}.${month}.${year}`;
                }
            } else {
                deadlineDisplay.textContent = 'ДД.ММ.ГГ';
            }
        });
    }

    // 3. Добавление новой задачи через AJAX
    if (addTaskForm) {
        addTaskForm.addEventListener('submit', function (e) {
            e.preventDefault();

            if (!taskTextInput) return;
            const text = taskTextInput.value.trim();
            if (text === '') return;

            const deadline = deadlineDate ? deadlineDate.value : null;

            fetch(addTaskForm.action, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json'
                },
                body: JSON.stringify({
                    task_text: text,
                    deadline: deadline || null
                })
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error('Ошибка сервера при добавлении задачи');
                }
                return response.json();
            })
            .then(data => {
                if (data.success && data.task) {
                    hideEmptyState();

                    const task = data.task;
                    const taskDiv = document.createElement('div');
                    taskDiv.className = 'all-tasks-task';
                    taskDiv.dataset.id = task.id;

                    const hasDeadlineClass = task.deadline_formatted ? 'has-deadline' : '';
                    const deadlineHtml = task.deadline_formatted
                        ? `<p class="task-deadline">до ${escapeHtml(task.deadline_formatted)}</p>`
                        : '';

                    taskDiv.innerHTML = `
                        <input type="checkbox" id="task_${task.id}">
                        <div class="task-text-content ${hasDeadlineClass}">
                            <div class="task-title-wrapper">
                                <label for="task_${task.id}" class="task-title" title="${escapeHtml(task.title)}">${escapeHtml(task.title)}</label>
                            </div>
                            ${deadlineHtml}
                        </div>
                        <div class="all-tasks-task-line"></div>
                    `;

                    tasksContainer.prepend(taskDiv);
                    setupReadMoreButtons();

                    // Очистка полей формы
                    taskTextInput.value = '';
                    if (deadlineDate) deadlineDate.value = '';
                    if (deadlineDisplay) deadlineDisplay.textContent = 'ДД.ММ.ГГ';
                    taskTextInput.focus();
                }
            })
            .catch(err => {
                console.error('Ошибка сохранения задачи:', err);
                // Если AJAX почему-то дал сбой, отправляем форму традиционным способом
                addTaskForm.submit();
            });
        });
    }

    // 4. Переключение статуса выполнения задачи (чекбокс)
    if (tasksContainer) {
        tasksContainer.addEventListener('change', function (e) {
            if (e.target.matches('input[type="checkbox"]')) {
                const taskDiv = e.target.closest('.all-tasks-task');
                const taskId = taskDiv ? taskDiv.dataset.id : null;

                if (taskId) {
                    fetch(`/tasks/${taskId}/toggle`, {
                        method: 'PATCH',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': csrfToken,
                            'Accept': 'application/json'
                        }
                    }).catch(err => {
                        console.error('Ошибка обновления статуса задачи:', err);
                    });
                }
            }
        });
    }
});
