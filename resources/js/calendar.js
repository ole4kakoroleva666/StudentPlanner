document.addEventListener('DOMContentLoaded', function () {
    const calendarGrid = document.querySelector('#calendarGrid');
    const calMonthSelect = document.querySelector('#calMonthSelect');
    const calYearSelect = document.querySelector('#calYearSelect');
    const calPrevBtn = document.querySelector('#calPrevBtn');
    const calNextBtn = document.querySelector('#calNextBtn');

    if (!calendarGrid || !calMonthSelect || !calYearSelect) return;

    const monthNames = [
        'Январь', 'Февраль', 'Март', 'Апрель', 'Май', 'Июнь',
        'Июль', 'Август', 'Сентябрь', 'Октябрь', 'Ноябрь', 'Декабрь'
    ];

    // Инициализация даты: если пришли с главной по ?date=YYYY-MM-DD — берем её
    const urlParams = new URLSearchParams(window.location.search);
    const dateParam = urlParams.get('date');
    let displayDate = dateParam ? new Date(dateParam) : new Date();
    if (isNaN(displayDate.getTime())) displayDate = new Date();

    // Заполняем месяцы в <select>
    monthNames.forEach((name, index) => {
        const option = document.createElement('option');
        option.value = index;
        option.textContent = name;
        calMonthSelect.appendChild(option);
    });

    // Заполняем года (±10 лет от текущего года)
    const currentYear = new Date().getFullYear();
    const startYear = currentYear - 10;
    const endYear = currentYear + 10;
    for (let y = startYear; y <= endYear; y++) {
        const option = document.createElement('option');
        option.value = y;
        option.textContent = y;
        calYearSelect.appendChild(option);
    }

    // ========================================================
    // ФУНКЦИЯ ПОЛУЧЕНИЯ СОБЫТИЙ (ГОТОВА ПОД БУДУЩЕЕ API)
    // ========================================================
    async function fetchCalendarEvents(year, month) {
        // ВРЕМЕННЫЕ МОК-ДАННЫЕ:
        // Формат: "ГГГГ-ММ-ДД": { lessons: число_пар, homework: число_дз }
        const padMonth = String(month + 1).padStart(2, '0');
        return {
            [`${year}-${padMonth}-04`]: { lessons: 1, homework: 0 },
            [`${year}-${padMonth}-16`]: { lessons: 1, homework: 1 },
            [`${year}-${padMonth}-20`]: { lessons: 2, homework: 3 }
        };

        // КОГДА БУДЕТ ГОТОВО API (Сетевой Город / Laravel):
        // const res = await fetch(`/api/calendar/events?year=${year}&month=${month + 1}`);
        // return await res.json();
    }

    // ========================================================
    // ОТРИСОВКА КАЛЕНДАРЯ
    // ========================================================
    async function renderCalendar() {
        const year = displayDate.getFullYear();
        const month = displayDate.getMonth();

        // Синхронизация select
        if (!calYearSelect.querySelector(`option[value="${year}"]`)) {
            const opt = document.createElement('option');
            opt.value = year;
            opt.textContent = year;
            if (year < startYear) calYearSelect.prepend(opt);
            else calYearSelect.appendChild(opt);
        }
        calMonthSelect.value = month;
        calYearSelect.value = year;

        // Запрашиваем события на месяц
        const events = await fetchCalendarEvents(year, month);

        calendarGrid.innerHTML = '';

        const firstDayIndex = new Date(year, month, 1).getDay();
        const daysInMonth = new Date(year, month + 1, 0).getDate();
        const prevMonthLastDay = new Date(year, month, 0).getDate();

        // Понедельник = 0
        const shift = (firstDayIndex === 0) ? 6 : firstDayIndex - 1;

        const now = new Date();
        const isCurrentMonth = (now.getFullYear() === year && now.getMonth() === month);
        const todayDate = now.getDate();

        // 1. Хвост предыдущего месяца
        for (let i = shift; i > 0; i--) {
            const dayNum = prevMonthLastDay - i + 1;
            const dayDiv = document.createElement('div');
            dayDiv.className = 'day prev-month';
            dayDiv.innerHTML = `<span class="day-number">${dayNum < 10 ? '0' + dayNum : dayNum}</span>`;
            
            const prevDate = new Date(year, month - 1, dayNum);
            const pMonth = String(prevDate.getMonth() + 1).padStart(2, '0');
            const pDay = String(dayNum).padStart(2, '0');
            const pDateStr = `${prevDate.getFullYear()}-${pMonth}-${pDay}`;

            dayDiv.style.cursor = 'pointer';
            dayDiv.addEventListener('click', function () {
                window.location.href = `/calendarOneDay?date=${pDateStr}`;
            });

            calendarGrid.appendChild(dayDiv);
        }

        // 2. Дни текущего месяца
        for (let day = 1; day <= daysInMonth; day++) {
            const dayDiv = document.createElement('div');
            dayDiv.className = 'day';

            const formattedMonth = String(month + 1).padStart(2, '0');
            const formattedDay = String(day).padStart(2, '0');
            const fullDateStr = `${year}-${formattedMonth}-${formattedDay}`;

            if (isCurrentMonth && day === todayDate) {
                dayDiv.classList.add('day-today');
            }

            // HTML для числа
            let dayHtml = `<span class="day-number">${formattedDay}</span>`;

            // Проверяем, есть ли события на эту дату
            const dayEvent = events[fullDateStr];
            if (dayEvent && (dayEvent.lessons > 0 || dayEvent.homework > 0)) {
                dayHtml += `
                    <div class="day-markers">
                        <div class="marker">
                            <img src="/media/lessonNew.svg" alt="lesson">
                            <span class="marker-count">${dayEvent.lessons || 0}</span>
                        </div>
                        <div class="marker">
                            <img src="/media/ex.svg" alt="homework">
                            <span class="marker-count">${dayEvent.homework || 0}</span>
                        </div>
                    </div>
                `;
            }

            dayDiv.innerHTML = dayHtml;

            // Клик по дню — переход на просмотр расписания дня
            dayDiv.addEventListener('click', function () {
                window.location.href = `/calendarOneDay?date=${fullDateStr}`;
            });

            calendarGrid.appendChild(dayDiv);
        }

        // 3. Хвост следующего месяца (до 42 ячеек)
        const totalRendered = shift + daysInMonth;
        const remainingDays = 42 - totalRendered;
        for (let nextDay = 1; nextDay <= remainingDays; nextDay++) {
            const dayDiv = document.createElement('div');
            dayDiv.className = 'day next-month';
            const formattedNext = nextDay < 10 ? '0' + nextDay : nextDay;
            dayDiv.innerHTML = `<span class="day-number">${formattedNext}</span>`;

            const nextDate = new Date(year, month + 1, nextDay);
            const nMonth = String(nextDate.getMonth() + 1).padStart(2, '0');
            const nDay = String(nextDay).padStart(2, '0');
            const nDateStr = `${nextDate.getFullYear()}-${nMonth}-${nDay}`;

            dayDiv.style.cursor = 'pointer';
            dayDiv.addEventListener('click', function () {
                window.location.href = `/calendarOneDay?date=${nDateStr}`;
            });

            calendarGrid.appendChild(dayDiv);
        }
    }

    // Слушатели событий
    calMonthSelect.addEventListener('change', function () {
        displayDate.setMonth(parseInt(this.value, 10));
        renderCalendar();
    });

    calYearSelect.addEventListener('change', function () {
        displayDate.setFullYear(parseInt(this.value, 10));
        renderCalendar();
    });

    if (calPrevBtn) {
        calPrevBtn.addEventListener('click', function () {
            displayDate.setMonth(displayDate.getMonth() - 1);
            renderCalendar();
        });
    }

    if (calNextBtn) {
        calNextBtn.addEventListener('click', function () {
            displayDate.setMonth(displayDate.getMonth() + 1);
            renderCalendar();
        });
    }

    renderCalendar();
});