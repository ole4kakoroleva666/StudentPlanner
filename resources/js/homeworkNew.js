document.addEventListener('DOMContentLoaded', function () {

    const STORAGE_KEY  = 'studentplanner_homework';
    const SUBJECTS_KEY = 'studentplanner_hw_subjects';

    const form          = document.querySelector('#newHomeworkForm');
    const subjectInput  = document.querySelector('#hwSubjectInput');
    const textInput     = document.querySelector('#hwTextInput');
    const deadlineInput = document.querySelector('#hwDeadlineInput');
    const datePicker    = document.querySelector('#hwDatePicker');
    const calendarIcon  = document.querySelector('#calendarIcon');
    const fileInput     = document.querySelector('#hwFileInput');
    const fileNameLabel = document.querySelector('#fileNameLabel');

    if (!form) return;

    // ========================================================
    // ПРЕДМЕТЫ: читаем / сохраняем список в localStorage
    // ========================================================
    function loadSubjects() {
        const raw = localStorage.getItem(SUBJECTS_KEY);
        return raw ? JSON.parse(raw) : [
            'Математика', 'Информатика', 'Физика', 'Химия',
            'История', 'Литература', 'Английский язык'
        ];
    }

    function saveSubjects(list) {
        localStorage.setItem(SUBJECTS_KEY, JSON.stringify(list));
    }

    function addSubjectToMemory(subject) {
        const subjects = loadSubjects();
        const s = subject.trim();
        if (s && !subjects.includes(s)) {
            subjects.push(s);
            saveSubjects(subjects);
        }
    }

    // ========================================================
    // АВТОДОПОЛНЕНИЕ (подсказки) для поля «Предмет»
    // ========================================================
    const suggestionBox = document.createElement('div');
    suggestionBox.className = 'subject-suggestions';
    subjectInput.parentNode.style.position = 'relative';
    subjectInput.parentNode.appendChild(suggestionBox);

    subjectInput.addEventListener('input', function () {
        const query   = this.value.trim().toLowerCase();
        const matches = loadSubjects().filter(s => s.toLowerCase().startsWith(query) && query.length > 0);

        if (matches.length === 0) {
            suggestionBox.style.display = 'none';
            return;
        }

        suggestionBox.innerHTML = '';
        matches.forEach(subject => {
            const item = document.createElement('div');
            item.className   = 'suggestion-item';
            item.textContent = subject;
            item.addEventListener('click', () => {
                subjectInput.value = subject;
                suggestionBox.style.display = 'none';
                textInput.focus();
            });
            suggestionBox.appendChild(item);
        });
        suggestionBox.style.display = 'block';
    });

    document.addEventListener('click', e => {
        if (!subjectInput.contains(e.target) && !suggestionBox.contains(e.target)) {
            suggestionBox.style.display = 'none';
        }
    });

    // ========================================================
    // МАСКА ДЛЯ ПОЛЯ ДАТЫ — формат ДД.ММ.ГГ
    // Автоматически вставляет точки, блокирует буквы
    // ========================================================
    if (deadlineInput) {
        deadlineInput.addEventListener('keydown', function (e) {
            const allowed = ['Backspace', 'Delete', 'ArrowLeft', 'ArrowRight', 'Tab'];
            // Блокируем всё, кроме цифр и служебных клавиш
            if (!allowed.includes(e.key) && !/^\d$/.test(e.key)) {
                e.preventDefault();
            }
        });

        deadlineInput.addEventListener('input', function () {
            // Оставляем только цифры
            let digits = this.value.replace(/\D/g, '');

            // Автоматически вставляем точки в нужных местах
            let masked = '';
            if (digits.length <= 2) {
                masked = digits;
            } else if (digits.length <= 4) {
                masked = digits.slice(0, 2) + '.' + digits.slice(2);
            } else {
                masked = digits.slice(0, 2) + '.' + digits.slice(2, 4) + '.' + digits.slice(4, 6);
            }

            this.value = masked;

            // Когда дата введена полностью (ДД.ММ.ГГ = 8 символов) — синхронизируем со скрытым datePicker
            if (masked.length === 8 && datePicker) {
                const [d, m, y] = masked.split('.');
                datePicker.value = `20${y}-${m}-${d}`;
            }
        });
    }

    // ========================================================
    // ИКОНКА КАЛЕНДАРЯ — открывает нативный календарь
    // ========================================================
    if (calendarIcon && datePicker) {
        calendarIcon.addEventListener('click', function (e) {
            e.preventDefault();
            if (typeof datePicker.showPicker === 'function') {
                datePicker.showPicker();
            } else {
                datePicker.focus();
                datePicker.click();
            }
        });

        // Когда дата выбрана в календаре — форматируем её в ДД.ММ.ГГ
        datePicker.addEventListener('change', function () {
            if (!this.value) return;
            const [fullYear, m, d] = this.value.split('-');
            const y = fullYear.slice(2);
            deadlineInput.value = `${d}.${m}.${y}`;
            clearError(deadlineInput);
        });
    }

    // ========================================================
    // ФАЙЛ — чтение в base64 и отображение имени
    // ========================================================
    let savedFileData = null;
    let savedFileName = null;

    if (fileInput && fileNameLabel) {
        fileInput.addEventListener('change', function () {
            if (!this.files || !this.files[0]) {
                fileNameLabel.textContent = 'Выбрать файл';
                savedFileData = null;
                savedFileName = null;
                return;
            }

            const file = this.files[0];
            savedFileName = file.name;
            fileNameLabel.textContent = file.name;

            const reader = new FileReader();
            reader.onload = function (e) {
                savedFileData = e.target.result;
            };
            reader.readAsDataURL(file);
        });
    }

    // ========================================================
    // ВАЛИДАЦИЯ
    // ========================================================
    function showError(input, message) {
        clearError(input);
        const err = document.createElement('p');
        err.className   = 'field-error';
        err.textContent = message;
        input.parentNode.appendChild(err);
        input.style.borderColor = '#EB5757';
    }

    function clearError(input) {
        const err = input.parentNode.querySelector('.field-error');
        if (err) err.remove();
        input.style.borderColor = '';
    }

    function isValidDate(str) {
        if (!/^\d{2}\.\d{2}\.\d{2}$/.test(str)) return false;
        const [d, m, y] = str.split('.').map(Number);
        const date = new Date(2000 + y, m - 1, d);
        return date.getFullYear() === 2000 + y && date.getMonth() === m - 1 && date.getDate() === d;
    }

    // ========================================================
    // ОТПРАВКА ФОРМЫ
    // ========================================================
    form.addEventListener('submit', function (e) {
        e.preventDefault();

        const subject  = subjectInput.value.trim();
        const text     = textInput.value.trim();
        const deadline = deadlineInput.value.trim();

        let hasError = false;

        if (!subject) {
            showError(subjectInput, 'Укажите предмет');
            hasError = true;
        } else {
            clearError(subjectInput);
        }

        if (!text) {
            showError(textInput, 'Опишите задание');
            hasError = true;
        } else {
            clearError(textInput);
        }

        // Дата необязательна, но если введена не до конца или неверно — предупреждаем
        if (deadline && !isValidDate(deadline)) {
            showError(deadlineInput, 'Формат даты: ДД.ММ.ГГ (например 15.09.25)');
            hasError = true;
        } else {
            clearError(deadlineInput);
        }

        if (hasError) return;

        // Сохраняем задание в localStorage
        const existing = JSON.parse(localStorage.getItem(STORAGE_KEY) || '[]');
        const newId    = existing.length > 0 ? Math.max(...existing.map(h => h.id)) + 1 : 1;

        existing.push({
            id:       newId,
            subject:  subject,
            text:     text,
            deadline: deadline || null,
            added:    Date.now(),
            fileName: savedFileName,
            fileData: savedFileData,
        });

        localStorage.setItem(STORAGE_KEY, JSON.stringify(existing));
        addSubjectToMemory(subject);

        showSuccessToast(`ДЗ по предмету «${subject}» добавлено!`);
        setTimeout(() => {
            window.location.href = '/homework';
        }, 1100);
    });

    // ========================================================
    // УВЕДОМЛЕНИЕ (TOAST)
    // ========================================================
    function showSuccessToast(message) {
        const toast = document.createElement('div');
        toast.className   = 'hw-toast';
        toast.textContent = '✓ ' + message;
        document.body.appendChild(toast);
        setTimeout(() => toast.classList.add('hw-toast--visible'), 50);
        setTimeout(() => {
            toast.classList.remove('hw-toast--visible');
            setTimeout(() => toast.remove(), 400);
        }, 1000);
    }
});
