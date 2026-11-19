document.addEventListener('DOMContentLoaded', function () {
    const oneDayDateText = document.querySelector('#oneDayDateText');
    const oneDayWeekText = document.querySelector('#oneDayWeekText');
    const lessonsContainer = document.querySelector('#lessonsContainer');
    const backToCalendarBtn = document.querySelector('#backToCalendarBtn');

    const dayNoteInput = document.querySelector('#dayNoteInput');
    const notesList = document.querySelector('#notesList');
    const hwList = document.querySelector('#hwList');

    const monthNamesGenitive = [
        'января', 'февраля', 'марта', 'апреля', 'мая', 'июня',
        'июля', 'августа', 'сентября', 'октября', 'ноября', 'декабря'
    ];

    const weekDayNames = [
        'Воскресенье', 'Понедельник', 'Вторник', 'Среда', 'Четверг', 'Пятница', 'Суббота'
    ];

    // ========================================================
    // 1. СЧИТЫВАНИЕ ДАТЫ ИЗ URL (?date=YYYY-MM-DD)
    // ========================================================
    const urlParams = new URLSearchParams(window.location.search);
    const dateParam = urlParams.get('date');

    let currentDate = dateParam ? new Date(dateParam) : new Date();
    if (isNaN(currentDate.getTime())) currentDate = new Date();

    const year = currentDate.getFullYear();
    const month = currentDate.getMonth();
    const day = currentDate.getDate();
    const dayOfWeek = currentDate.getDay();

    const fullDateStr = `${year}-${String(month + 1).padStart(2, '0')}-${String(day).padStart(2, '0')}`;

    // Отображаем дату и день недели в шапке
    if (oneDayDateText) {
        oneDayDateText.textContent = `${day} ${monthNamesGenitive[month]}`;
    }
    if (oneDayWeekText) {
        oneDayWeekText.textContent = weekDayNames[dayOfWeek];
    }

    // Кнопка «Назад» возвращает в календарь именно к этому месяцу
    if (backToCalendarBtn) {
        backToCalendarBtn.href = `/calendar?date=${fullDateStr}`;
    }

    // ========================================================
    // 2. ПОЛУЧЕНИЕ И ОТРИСОВКА РАСПИСАНИЯ (ЗАДЕЛ ПОД API)
    // ========================================================
    async function fetchDaySchedule(dateStr) {
        // Воскресенье — пар нет
        if (dayOfWeek === 0) {
            return [];
        }

        // ВРЕМЕННЫЕ МОК-ДАННЫЕ:
        return [
            {
                time: "08:30 — 10:05",
                subject: "Математический анализ",
                classroom: "2-204",
                teacher: "Иванова Ольга Сергеевна"
            },
            {
                time: "10:15 — 11:50",
                subject: "Проектирование и разработка интерфейсов",
                classroom: "1-306",
                teacher: "Гунько Григорий Александрович"
            },
            {
                time: "12:30 — 14:05",
                subject: "Базы данных",
                classroom: "3-102",
                teacher: "Смирнов Алексей Викторович"
            },
            {
                time: "14:15 — 15:50",
                subject: "Компьютерные сети",
                classroom: "2-110",
                teacher: "Кузнецов Дмитрий Павлович"
            }
        ];

        // КОГДА БУДЕТ ГОТОВО API (Сетевой Город / Laravel):
        // const res = await fetch(`/api/schedule/day?date=${dateStr}`);
        // return await res.json();
    }

    async function renderLessons() {
        if (!lessonsContainer) return;

        const lessons = await fetchDaySchedule(fullDateStr);
        lessonsContainer.innerHTML = '';

        if (!lessons || lessons.length === 0) {
            lessonsContainer.innerHTML = `
                <div style="text-align: center; padding: 4rem 1rem; color: #9AA0C3; font-size: 1.8rem; font-family: 'RobotoRegular';">
                    🎉 В этот день занятий нет
                </div>
            `;
            return;
        }

        lessons.forEach(lesson => {
            const lessonDiv = document.createElement('div');
            lessonDiv.className = 'onelesson';
            lessonDiv.innerHTML = `
                <div class="time">
                    <img class="icons" src="/media/time.svg" alt="time" />
                    <p>Время: ${lesson.time}</p>
                </div>
                <div class="subject">
                    <img class="icons" src="/media/books.svg" alt="subject" />
                    <p>Предмет: ${lesson.subject}</p>
                </div>
                <div class="classroom">
                    <img class="icons" src="/media/chool.svg" alt="classroom" />
                    <p>Аудитория: ${lesson.classroom}</p>
                </div>
                <div class="teacher">
                    <img class="icons" src="/media/teacher.svg" alt="teacher" />
                    <p>Преподаватель: ${lesson.teacher}</p>
                </div>
            `;
            lessonsContainer.appendChild(lessonDiv);
        });
    }

    renderLessons();

    // ========================================================
    // 3. ДОБАВЛЕНИЕ ЗАМЕТОК ЧЕРЕЗ ПОЛЕ ВВОДА (ПО КЛАВИШЕ ENTER)
    // ========================================================
    if (dayNoteInput && notesList) {
        dayNoteInput.addEventListener('keydown', function (e) {
            if (e.key === 'Enter') {
                const text = dayNoteInput.value.trim();
                if (text === '') return;

                const newId = 'note_' + Date.now();
                const noteDiv = document.createElement('div');
                noteDiv.className = 'note-task';
                noteDiv.innerHTML = `
                    <input type="checkbox" id="${newId}">
                    <label for="${newId}">${text}</label>
                `;

                // Вставляем заметку наверх списка
                notesList.prepend(noteDiv);

                // Очищаем поле ввода
                dayNoteInput.value = '';
            }
        });
    }

    // ========================================================
    // 4. ЗАЧЕРКИВАНИЕ ПРИ КЛИКЕ НА ЧЕКБОКС (ДЗ И ЗАМЕТКИ)
    // ========================================================
    function handleCheckboxToggle(container) {
        if (!container) return;
        container.addEventListener('change', function (e) {
            if (e.target.matches('input[type="checkbox"]')) {
                const label = e.target.parentElement.querySelector('label');
                if (label) {
                    label.style.textDecoration = e.target.checked ? 'line-through' : 'none';
                    label.style.color = e.target.checked ? '#9AA0C3' : '#3C3E4C';
                }
            }
        });
    }

    handleCheckboxToggle(hwList);
    handleCheckboxToggle(notesList);
});