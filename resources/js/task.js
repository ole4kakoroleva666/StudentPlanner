document.addEventListener('DOMContentLoaded', function () {
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

    const tasksContainer = document.querySelector('#tasksContainer');
    const addTaskForm = document.querySelector('#addTaskForm');
    const taskTextInput = document.querySelector('#taskTextInput');
    const deadlineDate = document.querySelector('#deadlineDate');
    const deadlineDisplay = document.querySelector('#deadlineDisplay');
    const deadlinePickerBtn = document.querySelector('#deadlinePickerBtn');
    const deadlineClearBtn = document.querySelector('#deadlineClearBtn');

    // Счётчики
    const countAll = document.querySelector('#countAll');
    const countTodo = document.querySelector('#countTodo');
    const countInProgress = document.querySelector('#countInProgress');
    const countCompleted = document.querySelector('#countCompleted');
    const progressText = document.querySelector('#progressText');

    // Фильтры
    const filterButtons = document.querySelectorAll('.tasks-filter-btn');
    let currentFilter = 'todo'; // По умолчанию показываем только задачи "К выполнению"!

    function escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }

    function hideEmptyState() {
        const empty = document.querySelector('#emptyState');
        if (empty) empty.remove();
    }

    function showEmptyStateIfNeeded() {
        if (!tasksContainer) return;
        const visibleTasks = Array.from(tasksContainer.querySelectorAll('.task-item')).filter(
            el => el.style.display !== 'none'
        );
        let empty = document.querySelector('#emptyState');

        if (visibleTasks.length === 0) {
            if (!empty) {
                empty = document.createElement('div');
                empty.className = 'empty-state';
                empty.id = 'emptyState';
                let msg = 'В этой категории пока нет задач.';
                if (currentFilter === 'todo') msg = 'Все задачи распределены! Добавьте новую задачу выше.';
                if (currentFilter === 'in_progress') msg = 'Сейчас ничего нет в работе. Нажмите «В работу» на любой задаче!';
                if (currentFilter === 'completed') msg = 'Выполненных задач пока нет.';
                empty.innerHTML = `<p>${msg}</p>`;
                tasksContainer.appendChild(empty);
            }
        } else {
            if (empty) empty.remove();
        }
    }

    // Обновление счётчиков задач
    function updateCounters() {
        if (!tasksContainer) return;
        const allTasks = tasksContainer.querySelectorAll('.task-item');
        let todo = 0;
        let inProgress = 0;
        let completed = 0;

        allTasks.forEach(task => {
            const st = task.dataset.status;
            if (st === 'completed') completed++;
            else if (st === 'in_progress') inProgress++;
            else todo++;
        });

        const total = allTasks.length;

        if (countAll) countAll.textContent = total;
        if (countTodo) countTodo.textContent = todo;
        if (countInProgress) countInProgress.textContent = inProgress;
        if (countCompleted) countCompleted.textContent = completed;
        if (progressText) progressText.textContent = `${completed} из ${total}`;
    }

    // Применение текущего фильтра
    function applyFilter(filter) {
        currentFilter = filter;
        const tasks = tasksContainer.querySelectorAll('.task-item');

        tasks.forEach(task => {
            const status = task.dataset.status;
            if (filter === 'all') {
                task.style.display = 'flex';
            } else if (filter === 'todo') {
                task.style.display = (status === 'todo') ? 'flex' : 'none';
            } else if (filter === 'in_progress') {
                task.style.display = (status === 'in_progress') ? 'flex' : 'none';
            } else if (filter === 'completed') {
                task.style.display = (status === 'completed') ? 'flex' : 'none';
            }
        });

        showEmptyStateIfNeeded();
    }

    // Переключение табов фильтров
    filterButtons.forEach(btn => {
        btn.addEventListener('click', function () {
            filterButtons.forEach(b => b.classList.remove('active'));
            this.classList.add('active');
            applyFilter(this.dataset.filter);
        });
    });

    // 1. Выбор даты (срок сдачи)
    function openDatePicker() {
        if (!deadlineDate) return;
        if (typeof deadlineDate.showPicker === 'function') {
            deadlineDate.showPicker();
        } else {
            deadlineDate.focus();
            deadlineDate.click();
        }
    }

    if (deadlinePickerBtn) {
        deadlinePickerBtn.addEventListener('click', function (e) {
            if (e.target === deadlineClearBtn) return;
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
                    deadlineDisplay.textContent = `до ${day}.${month}.${year}`;
                    deadlinePickerBtn.classList.add('has-date');
                }
            } else {
                resetDeadlinePicker();
            }
        });
    }

    function resetDeadlinePicker() {
        if (deadlineDate) deadlineDate.value = '';
        if (deadlineDisplay) deadlineDisplay.textContent = 'Срок сдачи';
        if (deadlinePickerBtn) deadlinePickerBtn.classList.remove('has-date');
    }

    if (deadlineClearBtn) {
        deadlineClearBtn.addEventListener('click', function (e) {
            e.stopPropagation();
            resetDeadlinePicker();
        });
    }

    // 2. Создание новой задачи через AJAX
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
                if (!response.ok) throw new Error('Ошибка сервера при добавлении задачи');
                return response.json();
            })
            .then(data => {
                if (data.success && data.task) {
                    hideEmptyState();

                    const task = data.task;
                    const taskDiv = document.createElement('div');
                    taskDiv.className = 'task-item';
                    taskDiv.dataset.id = task.id;
                    taskDiv.dataset.status = 'todo';

                    const deadlineHtml = task.deadline_formatted
                        ? `<div class="task-deadline-badge" title="Срок сдачи">
                               <img src="/media/calendar.svg" alt="Календарь">
                               <span>до ${escapeHtml(task.deadline_formatted)}</span>
                           </div>`
                        : '';

                    taskDiv.innerHTML = `
                        <div class="task-checkbox-wrapper">
                            <input type="checkbox" id="task_${task.id}" class="task-checkbox" title="Отметить как выполненную">
                        </div>
                        <div class="task-body">
                            <div class="task-title-row">
                                <label for="task_${task.id}" class="task-text" title="${escapeHtml(task.title)}">
                                    ${escapeHtml(task.title)}
                                </label>
                            </div>
                        </div>
                        <div class="task-meta">
                            ${deadlineHtml}
                            <div class="task-action-buttons">
                                <button type="button" class="task-btn-status start" data-action="start" title="Взять в работу прямо сейчас">
                                    <span>В работу</span>
                                </button>
                            </div>
                            <button type="button" class="task-delete-btn" title="Удалить задачу" data-id="${task.id}">
                                <img src="/media/delete.svg" alt="Удалить">
                            </button>
                        </div>
                    `;

                    tasksContainer.prepend(taskDiv);

                    // Очистка формы
                    taskTextInput.value = '';
                    resetDeadlinePicker();
                    taskTextInput.focus();

                    updateCounters();
                    applyFilter(currentFilter);
                }
            })
            .catch(err => {
                console.error('Ошибка сохранения задачи:', err);
                addTaskForm.submit();
            });
        });
    }

    // Вспомогательная функция обновления карточки при смене статуса
    function updateTaskCardStatus(taskItem, newStatus) {
        taskItem.dataset.status = newStatus;
        taskItem.classList.toggle('is-completed', newStatus === 'completed');
        taskItem.classList.toggle('is-in-progress', newStatus === 'in_progress');

        const checkbox = taskItem.querySelector('.task-checkbox');
        if (checkbox) checkbox.checked = (newStatus === 'completed');

        // Бейдж "В процессе"
        const titleRow = taskItem.querySelector('.task-title-row');
        let inProgressBadge = titleRow ? titleRow.querySelector('.task-status-badge') : null;

        if (newStatus === 'in_progress') {
            if (!inProgressBadge && titleRow) {
                inProgressBadge = document.createElement('span');
                inProgressBadge.className = 'task-status-badge in-progress';
                inProgressBadge.textContent = 'В работе';
                titleRow.prepend(inProgressBadge);
            }
        } else {
            if (inProgressBadge) inProgressBadge.remove();
        }

        // Кнопка действия (В работу / Отложить / Вернуть)
        const actionWrapper = taskItem.querySelector('.task-action-buttons');
        if (actionWrapper) {
            if (newStatus === 'todo') {
                actionWrapper.innerHTML = `
                    <button type="button" class="task-btn-status start" data-action="start" title="Взять в работу прямо сейчас">
                        <span>В работу</span>
                    </button>
                `;
            } else if (newStatus === 'in_progress') {
                actionWrapper.innerHTML = `
                    <button type="button" class="task-btn-status pause" data-action="pause" title="Отложить обратно в список">
                        <span>Отложить</span>
                    </button>
                `;
            } else if (newStatus === 'completed') {
                actionWrapper.innerHTML = `
                    <button type="button" class="task-btn-status restart" data-action="restart" title="Вернуть задачу в список">
                        <span>Вернуть</span>
                    </button>
                `;
            }
        }

        updateCounters();

        // Если текущий фильтр не совпадает со статусом — плавно скрываем карточку
        if (currentFilter !== 'all') {
            const shouldHide = (
                (currentFilter === 'todo' && newStatus !== 'todo') ||
                (currentFilter === 'in_progress' && newStatus !== 'in_progress') ||
                (currentFilter === 'completed' && newStatus !== 'completed')
            );

            if (shouldHide) {
                taskItem.style.transition = 'opacity 0.25s ease, transform 0.25s ease';
                taskItem.style.opacity = '0';
                taskItem.style.transform = 'scale(0.95)';
                setTimeout(() => {
                    taskItem.style.display = 'none';
                    taskItem.style.opacity = '';
                    taskItem.style.transform = '';
                    showEmptyStateIfNeeded();
                }, 250);
            }
        }
    }

    // 3. Обработка кликов внутри списка (кнопки статуса + удаление)
    if (tasksContainer) {
        tasksContainer.addEventListener('click', function (e) {
            // Клик по кнопке действия ("В работу" / "Отложить" / "Вернуть")
            const actionBtn = e.target.closest('.task-btn-status');
            if (actionBtn) {
                const taskItem = actionBtn.closest('.task-item');
                const taskId = taskItem ? taskItem.dataset.id : null;
                const action = actionBtn.dataset.action;

                let nextStatus = 'todo';
                if (action === 'start') nextStatus = 'in_progress';
                if (action === 'pause') nextStatus = 'todo';
                if (action === 'restart') nextStatus = 'todo';

                if (taskId && taskItem) {
                    fetch(`/tasks/${taskId}/status`, {
                        method: 'PATCH',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': csrfToken,
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify({ status: nextStatus })
                    })
                    .then(res => res.json())
                    .then(data => {
                        if (data.success) {
                            updateTaskCardStatus(taskItem, data.status);
                        }
                    })
                    .catch(err => console.error('Ошибка изменения статуса:', err));
                }
                return;
            }

            // Клик по кнопке "Удалить"
            const deleteBtn = e.target.closest('.task-delete-btn');
            if (deleteBtn) {
                const taskId = deleteBtn.dataset.id;
                const taskItem = deleteBtn.closest('.task-item');

                if (taskId && taskItem) {
                    deleteBtn.style.pointerEvents = 'none';
                    taskItem.style.opacity = '0.3';
                    taskItem.style.transform = 'scale(0.96)';

                    fetch(`/tasks/${taskId}`, {
                        method: 'DELETE',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': csrfToken,
                            'Accept': 'application/json'
                        }
                    })
                    .then(res => res.json())
                    .then(data => {
                        if (data.success) {
                            taskItem.style.transition = 'all 0.25s ease';
                            taskItem.style.height = '0';
                            taskItem.style.padding = '0';
                            taskItem.style.margin = '0';
                            taskItem.style.opacity = '0';
                            setTimeout(() => {
                                taskItem.remove();
                                updateCounters();
                                showEmptyStateIfNeeded();
                            }, 250);
                        } else {
                            taskItem.style.opacity = '1';
                            taskItem.style.transform = 'none';
                            deleteBtn.style.pointerEvents = 'auto';
                        }
                    })
                    .catch(err => {
                        console.error('Ошибка удаления задачи:', err);
                        taskItem.style.opacity = '1';
                        taskItem.style.transform = 'none';
                        deleteBtn.style.pointerEvents = 'auto';
                    });
                }
                return;
            }
        });

        // 4. Переключение статуса чекбоксом (галочкой)
        tasksContainer.addEventListener('change', function (e) {
            if (e.target.matches('.task-checkbox')) {
                const taskItem = e.target.closest('.task-item');
                const taskId = taskItem ? taskItem.dataset.id : null;

                if (taskId && taskItem) {
                    fetch(`/tasks/${taskId}/toggle`, {
                        method: 'PATCH',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': csrfToken,
                            'Accept': 'application/json'
                        }
                    })
                    .then(res => res.json())
                    .then(data => {
                        if (data.success) {
                            updateTaskCardStatus(taskItem, data.status);
                        }
                    })
                    .catch(err => console.error('Ошибка переключения чекбокса:', err));
                }
            }
        });
    }

    // Инициализация при первой загрузке страницы:
    updateCounters();
    applyFilter('todo'); // Показываем сразу список текущих дел "К выполнению"
});
