import { confirmDelete } from './confirmModal';

document.addEventListener('DOMContentLoaded', function () {
    const oneDayDateText = document.querySelector('#oneDayDateText');
    const oneDayWeekText = document.querySelector('#oneDayWeekText');
    const lessonsContainer = document.querySelector('#lessonsContainer');
    const backToCalendarBtn = document.querySelector('#backToCalendarBtn');

    const dayNoteInput = document.querySelector('#dayNoteInput');
    const notesList = document.querySelector('#notesList');
    const hwList = document.querySelector('#hwList');
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';

    const monthNamesGenitive = [
        'января', 'февраля', 'марта', 'апреля', 'мая', 'июня',
        'июля', 'августа', 'сентября', 'октября', 'ноября', 'декабря'
    ];

    const weekDayNames = [
        'Воскресенье', 'Понедельник', 'Вторник', 'Среда', 'Четверг', 'Пятница', 'Суббота'
    ];

    const urlParams = new URLSearchParams(window.location.search);
    const dateParam = urlParams.get('date');

    let currentDate = dateParam ? new Date(dateParam) : new Date();
    if (isNaN(currentDate.getTime())) currentDate = new Date();

    const year = currentDate.getFullYear();
    const month = currentDate.getMonth();
    const day = currentDate.getDate();
    const dayOfWeek = currentDate.getDay();

    const fullDateStr = `${year}-${String(month + 1).padStart(2, '0')}-${String(day).padStart(2, '0')}`;

    if (oneDayDateText) {
        oneDayDateText.textContent = `${day} ${monthNamesGenitive[month]}`;
    }
    if (oneDayWeekText) {
        oneDayWeekText.textContent = weekDayNames[dayOfWeek];
    }
    if (backToCalendarBtn) {
        backToCalendarBtn.href = `/calendar?date=${fullDateStr}`;
    }

    function escapeHtml(str) {
        return String(str)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;');
    }

    let dayNotes = [];
    let dayHomework = [];

    function renderLessons(lessons) {
        if (!lessonsContainer) return;
        lessonsContainer.innerHTML = '';

        if (!lessons || lessons.length === 0) {
            lessonsContainer.innerHTML = `
                <div style="text-align: center; padding: 4rem 1rem; color: #9AA0C3; font-size: 1.8rem; font-family: 'RobotoRegular';">
                    В этот день занятий нет
                </div>
            `;
            return;
        }

        lessons.forEach((lesson) => {
            const lessonDiv = document.createElement('div');
            lessonDiv.className = 'onelesson';
            lessonDiv.innerHTML = `
                <div class="time">
                    <img class="icons" src="/media/time.svg" alt="time" />
                    <p>Время: ${escapeHtml(lesson.time)}</p>
                </div>
                <div class="subject">
                    <img class="icons" src="/media/books.svg" alt="subject" />
                    <p>Предмет: ${escapeHtml(lesson.subject)}</p>
                </div>
                <div class="classroom">
                    <img class="icons" src="/media/chool.svg" alt="classroom" />
                    <p>Аудитория: ${escapeHtml(lesson.classroom)}</p>
                </div>
                <div class="teacher">
                    <img class="icons" src="/media/teacher.svg" alt="teacher" />
                    <p>Преподаватель: ${escapeHtml(lesson.teacher)}</p>
                </div>
            `;
            lessonsContainer.appendChild(lessonDiv);
        });
    }

    function renderDayNotes() {
        if (!notesList) return;
        notesList.innerHTML = '';

        if (dayNotes.length === 0) {
            notesList.innerHTML = '<p style="color: #9AA0C3; font-size: 1.5rem; text-align: center; padding: 2rem 0;">На этот день заметок нет</p>';
            return;
        }

        dayNotes.forEach((note) => {
            const noteDiv = document.createElement('div');
            noteDiv.className = 'note-task';
            noteDiv.dataset.id = note.id;

            const isChecked = note.completed ? 'checked' : '';
            noteDiv.innerHTML = `
                <input type="checkbox" id="day_note_${note.id}" ${isChecked}>
                <label for="day_note_${note.id}">${escapeHtml(note.text)}</label>
                <button type="button" class="day-note-delete" title="Удалить заметку">×</button>
            `;
            notesList.appendChild(noteDiv);
        });
    }

    function renderDayHomework() {
        if (!hwList) return;
        hwList.innerHTML = '';

        if (dayHomework.length === 0) {
            hwList.innerHTML = '<p style="color: #9AA0C3; font-size: 1.5rem; text-align: center; padding: 2rem 0;">На этот день заданий нет</p>';
            return;
        }

        dayHomework.forEach((hw) => {
            const hwItem = document.createElement('div');
            hwItem.className = 'homework-task';
            hwItem.dataset.id = hw.id;

            const titleFull = (hw.subject ? hw.subject + ': ' : '') + hw.text;
            hwItem.innerHTML = `
                <input type="checkbox" id="day_hw_${hw.id}">
                <label for="day_hw_${hw.id}">${escapeHtml(titleFull)}</label>
            `;
            hwList.appendChild(hwItem);
        });
    }

    async function loadDayData() {
        try {
            const res = await fetch(`/calendar/day?date=${fullDateStr}`, {
                headers: { 'Accept': 'application/json' },
            });
            if (!res.ok) return;
            const data = await res.json();
            renderLessons(data.lessons || []);
            dayHomework = data.homeworks || [];
            dayNotes = data.notes || [];
            renderDayHomework();
            renderDayNotes();
        } catch {
            renderLessons([]);
            renderDayHomework();
            renderDayNotes();
        }
    }

    if (dayNoteInput && notesList) {
        dayNoteInput.addEventListener('keydown', async function (e) {
            if (e.key !== 'Enter') return;
            e.preventDefault();
            const text = dayNoteInput.value.trim();
            if (!text) return;

            dayNoteInput.value = '';

            try {
                const res = await fetch('/calendar/day-notes', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify({
                        date: fullDateStr,
                        content: text,
                    }),
                });
                if (!res.ok) return;
                const data = await res.json();
                if (data.note) {
                    dayNotes.push(data.note);
                    renderDayNotes();
                }
            } catch {
                // ignore
            }
        });

        notesList.addEventListener('change', async function (e) {
            if (!e.target.matches('input[type="checkbox"]')) return;
            const itemDiv = e.target.closest('.note-task');
            if (!itemDiv) return;
            const id = Number(itemDiv.dataset.id);

            try {
                await fetch(`/calendar/day-notes/${id}/toggle`, {
                    method: 'PATCH',
                    headers: {
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json',
                    },
                });
                const target = dayNotes.find((n) => n.id === id);
                if (target) target.completed = e.target.checked;
            } catch {
                // ignore
            }
        });

        notesList.addEventListener('click', async function (e) {
            const delBtn = e.target.closest('.day-note-delete');
            if (!delBtn) return;
            const itemDiv = delBtn.closest('.note-task');
            if (!itemDiv) return;
            const id = Number(itemDiv.dataset.id);
            const target = dayNotes.find((n) => n.id === id);

            const confirmed = await confirmDelete({
                title: 'Удалить заметку?',
                message: target
                    ? `Вы уверены, что хотите удалить заметку «${target.text}»?`
                    : 'Вы уверены, что хотите удалить эту заметку?',
            });
            if (!confirmed) return;

            try {
                const res = await fetch(`/calendar/day-notes/${id}`, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json',
                    },
                });
                if (res.ok) {
                    dayNotes = dayNotes.filter((n) => n.id !== id);
                    renderDayNotes();
                }
            } catch {
                // ignore
            }
        });
    }

    loadDayData();
});
