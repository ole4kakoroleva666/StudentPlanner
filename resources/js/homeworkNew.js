document.addEventListener('DOMContentLoaded', function () {
    const SUBJECTS_KEY = 'studentplanner_hw_subjects';

    const form = document.querySelector('#newHomeworkForm');
    const subjectInput = document.querySelector('#hwSubjectInput');
    const textInput = document.querySelector('#hwTextInput');
    const deadlineInput = document.querySelector('#hwDeadlineInput');
    const datePicker = document.querySelector('#hwDatePicker');
    const calendarIcon = document.querySelector('#calendarIcon');
    const fileInput = document.querySelector('#hwFileInput');
    const fileNameLabel = document.querySelector('#fileNameLabel');
    const submitBtn = document.querySelector('#addHwSubmitBtn');
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';

    if (!form) return;

    const storeUrl = form.dataset.storeUrl || '/homework';
    let serverSubjects = [];
    try {
        serverSubjects = JSON.parse(form.dataset.subjects || '[]') || [];
    } catch {
        serverSubjects = [];
    }

    function loadSubjects() {
        const raw = localStorage.getItem(SUBJECTS_KEY);
        const local = raw
            ? JSON.parse(raw)
            : ['Математика', 'Информатика', 'Физика', 'Химия', 'История', 'Литература', 'Английский язык'];
        return Array.from(new Set([...serverSubjects, ...local]));
    }

    function addSubjectToMemory(subject) {
        const subjects = loadSubjects();
        const s = subject.trim();
        if (s && !subjects.includes(s)) {
            subjects.push(s);
            localStorage.setItem(SUBJECTS_KEY, JSON.stringify(subjects));
        }
    }

    const suggestionBox = document.createElement('div');
    suggestionBox.className = 'subject-suggestions';
    subjectInput.parentNode.style.position = 'relative';
    subjectInput.parentNode.appendChild(suggestionBox);

    subjectInput.addEventListener('input', function () {
        const query = this.value.trim().toLowerCase();
        const matches = loadSubjects().filter((s) => s.toLowerCase().startsWith(query) && query.length > 0);

        if (matches.length === 0) {
            suggestionBox.style.display = 'none';
            return;
        }

        suggestionBox.innerHTML = '';
        matches.forEach((subject) => {
            const item = document.createElement('div');
            item.className = 'suggestion-item';
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

    document.addEventListener('click', (e) => {
        if (!subjectInput.contains(e.target) && !suggestionBox.contains(e.target)) {
            suggestionBox.style.display = 'none';
        }
    });

    if (deadlineInput) {
        deadlineInput.addEventListener('keydown', function (e) {
            const allowed = ['Backspace', 'Delete', 'ArrowLeft', 'ArrowRight', 'Tab'];
            if (!allowed.includes(e.key) && !/^\d$/.test(e.key)) {
                e.preventDefault();
            }
        });

        deadlineInput.addEventListener('input', function () {
            const digits = this.value.replace(/\D/g, '');
            let masked = '';
            if (digits.length <= 2) {
                masked = digits;
            } else if (digits.length <= 4) {
                masked = digits.slice(0, 2) + '.' + digits.slice(2);
            } else {
                masked = digits.slice(0, 2) + '.' + digits.slice(2, 4) + '.' + digits.slice(4, 6);
            }
            this.value = masked;

            if (masked.length === 8 && datePicker) {
                const [d, m, y] = masked.split('.');
                datePicker.value = `20${y}-${m}-${d}`;
            }
        });
    }

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

        datePicker.addEventListener('change', function () {
            if (!this.value) return;
            const [fullYear, m, d] = this.value.split('-');
            deadlineInput.value = `${d}.${m}.${fullYear.slice(2)}`;
            clearError(deadlineInput);
        });
    }

    if (fileInput && fileNameLabel) {
        fileInput.addEventListener('change', function () {
            if (!this.files || !this.files[0]) {
                fileNameLabel.textContent = 'Выбрать файл';
                return;
            }
            fileNameLabel.textContent = this.files[0].name;
        });
    }

    function showError(input, message) {
        clearError(input);
        const err = document.createElement('p');
        err.className = 'field-error';
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

    function toIsoDate(str) {
        const [d, m, y] = str.split('.');
        return `20${y}-${m}-${d}`;
    }

    form.addEventListener('submit', async function (e) {
        e.preventDefault();

        const subject = subjectInput.value.trim();
        const text = textInput.value.trim();
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

        if (deadline && !isValidDate(deadline)) {
            showError(deadlineInput, 'Формат даты: ДД.ММ.ГГ (например 15.09.25)');
            hasError = true;
        } else {
            clearError(deadlineInput);
        }

        if (hasError) return;

        const formData = new FormData();
        formData.append('subject', subject);
        formData.append('description', text);
        if (deadline) {
            formData.append('deadline', toIsoDate(deadline));
        }
        if (fileInput?.files?.[0]) {
            formData.append('file', fileInput.files[0]);
        }

        if (submitBtn) submitBtn.disabled = true;

        try {
            const res = await fetch(storeUrl, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json',
                },
                body: formData,
            });
            const data = await res.json();

            if (!res.ok) {
                const msg =
                    data?.errors?.subject?.[0] ||
                    data?.errors?.description?.[0] ||
                    data?.errors?.deadline?.[0] ||
                    data?.errors?.file?.[0] ||
                    data?.message ||
                    'Ошибка сохранения';
                showError(textInput, msg);
                return;
            }

            addSubjectToMemory(subject);
            showSuccessToast(`ДЗ по предмету «${subject}» добавлено!`);
            setTimeout(() => {
                window.location.href = '/homework';
            }, 900);
        } catch {
            showError(textInput, 'Ошибка соединения с сервером');
        } finally {
            if (submitBtn) submitBtn.disabled = false;
        }
    });

    function showSuccessToast(message) {
        const toast = document.createElement('div');
        toast.className = 'hw-toast';
        toast.textContent = '✓ ' + message;
        document.body.appendChild(toast);
        setTimeout(() => toast.classList.add('hw-toast--visible'), 50);
        setTimeout(() => {
            toast.classList.remove('hw-toast--visible');
            setTimeout(() => toast.remove(), 400);
        }, 1000);
    }
});
