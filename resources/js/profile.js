import { confirmDelete } from './confirmModal.js';

document.addEventListener('DOMContentLoaded', () => {
    const page = document.getElementById('profilePage');
    if (!page) return;

    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
    const avatarUrl = page.dataset.avatarUrl;
    const avatarDeleteUrl = page.dataset.avatarDeleteUrl;
    const infoUrl = page.dataset.infoUrl;
    const passwordUrl = page.dataset.passwordUrl;

    const toastEl = document.getElementById('profileToast');
    let toastTimer = null;

    function showToast(message, isError = false) {
        if (!toastEl) return;
        toastEl.textContent = message;
        toastEl.classList.toggle('is-error', isError);
        toastEl.classList.add('is-visible');
        clearTimeout(toastTimer);
        toastTimer = setTimeout(() => {
            toastEl.classList.remove('is-visible');
        }, 3000);
    }

    // ==========================
    // Загрузка и удаление аватарки
    // ==========================
    const avatarInput = document.getElementById('avatarInput');
    const avatarImage = document.getElementById('avatarImage');
    const avatarDropzone = document.getElementById('avatarDropzone');
    const deleteAvatarBtn = document.getElementById('deleteAvatarBtn');
    const headerAvatarImg = document.querySelector('[data-header-avatar]');

    function syncHeaderAvatar(url, isCustom) {
        if (!headerAvatarImg) return;
        if (isCustom) {
            headerAvatarImg.src = url;
            headerAvatarImg.classList.add('header-avatar');
        } else {
            headerAvatarImg.src = headerAvatarImg.dataset.defaultSrc || url;
            headerAvatarImg.classList.remove('header-avatar');
        }
    }

    async function uploadAvatarFile(file) {
        if (!file) return;

        const allowedTypes = ['image/jpeg', 'image/png', 'image/webp', 'image/gif'];
        if (!allowedTypes.includes(file.type)) {
            showToast('Допустимые форматы: JPG, PNG, WEBP, GIF', true);
            return;
        }
        if (file.size > 5 * 1024 * 1024) {
            showToast('Размер изображения не должен превышать 5 МБ', true);
            return;
        }

        const formData = new FormData();
        formData.append('avatar', file);

        try {
            const response = await fetch(avatarUrl, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json',
                },
                body: formData,
            });

            const data = await response.json();

            if (!response.ok) {
                const firstError = data?.errors?.avatar?.[0] || data?.message || 'Ошибка загрузки аватарки';
                showToast(firstError, true);
                return;
            }

            const freshUrl = `${data.avatar_url}?t=${Date.now()}`;
            avatarImage.src = freshUrl;
            avatarImage.classList.add('is-custom');
            avatarDropzone?.classList.add('has-custom-avatar');
            if (deleteAvatarBtn) deleteAvatarBtn.hidden = false;
            syncHeaderAvatar(freshUrl, true);
            showToast(data.message || 'Аватарка обновлена');
        } catch {
            showToast('Не удалось загрузить изображение', true);
        } finally {
            avatarInput.value = '';
        }
    }

    avatarInput?.addEventListener('change', () => {
        if (avatarInput.files && avatarInput.files[0]) {
            uploadAvatarFile(avatarInput.files[0]);
        }
    });

    if (avatarDropzone) {
        ['dragenter', 'dragover'].forEach((evt) => {
            avatarDropzone.addEventListener(evt, (e) => {
                e.preventDefault();
                avatarDropzone.classList.add('is-dragover');
            });
        });

        ['dragleave', 'drop'].forEach((evt) => {
            avatarDropzone.addEventListener(evt, (e) => {
                e.preventDefault();
                avatarDropzone.classList.remove('is-dragover');
            });
        });

        avatarDropzone.addEventListener('drop', (e) => {
            const file = e.dataTransfer?.files?.[0];
            if (file) uploadAvatarFile(file);
        });
    }

    deleteAvatarBtn?.addEventListener('click', async () => {
        const confirmed = await confirmDelete({
            title: 'Удалить аватарку?',
            message: 'Будет восстановлена стандартная иконка профиля.',
            confirmText: 'Удалить',
            cancelText: 'Отмена',
        });
        if (!confirmed) return;

        try {
            const response = await fetch(avatarDeleteUrl, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json',
                },
            });

            const data = await response.json();
            if (!response.ok) {
                showToast(data?.message || 'Не удалось удалить аватарку', true);
                return;
            }

            avatarImage.src = data.avatar_url;
            avatarImage.classList.remove('is-custom');
            avatarDropzone?.classList.remove('has-custom-avatar');
            deleteAvatarBtn.hidden = true;
            syncHeaderAvatar(data.avatar_url, false);
            showToast(data.message || 'Аватарка удалена');
        } catch {
            showToast('Ошибка при удалении аватарки', true);
        }
    });

    // ==========================
    // Редактирование полей профиля
    // ==========================
    const nicknameEl = document.getElementById('profileNickname');
    const groupBadgeEl = document.getElementById('profileGroupBadge');

    function closeEditor(fieldName) {
        const field = page.querySelector(`.info-tile[data-field="${fieldName}"]`);
        if (!field) return;

        const display = field.querySelector(`[data-field-display="${fieldName}"]`);
        const editor = field.querySelector(`[data-editor="${fieldName}"]`);
        const errorEl = field.querySelector(`[data-error="${fieldName}"]`);
        const editBtn = field.querySelector(`[data-edit-trigger="${fieldName}"]`);

        field.classList.remove('is-editing');
        if (editor) editor.hidden = true;
        if (display) display.hidden = false;
        if (editBtn) editBtn.hidden = false;
        if (errorEl) errorEl.textContent = '';
    }

    function openEditor(fieldName) {
        const field = page.querySelector(`.info-tile[data-field="${fieldName}"]`);
        if (!field) return;

        const display = field.querySelector(`[data-field-display="${fieldName}"]`);
        const editor = field.querySelector(`[data-editor="${fieldName}"]`);
        const errorEl = field.querySelector(`[data-error="${fieldName}"]`);
        const editBtn = field.querySelector(`[data-edit-trigger="${fieldName}"]`);

        field.classList.add('is-editing');
        if (errorEl) errorEl.textContent = '';
        if (display) display.hidden = true;
        if (editBtn) editBtn.hidden = true;
        if (editor) {
            editor.hidden = false;
            const firstInput = editor.querySelector('.field-input');
            firstInput?.focus();
        }
    }

    page.querySelectorAll('[data-edit-trigger]').forEach((btn) => {
        btn.addEventListener('click', () => {
            openEditor(btn.dataset.editTrigger);
        });
    });

    page.querySelectorAll('[data-cancel]').forEach((btn) => {
        btn.addEventListener('click', () => {
            const fieldName = btn.dataset.cancel;
            if (fieldName === 'password') {
                ['currentPassword', 'newPassword', 'newPasswordConfirm'].forEach((id) => {
                    const input = document.getElementById(id);
                    if (input) input.value = '';
                });
            }
            closeEditor(fieldName);
        });
    });

    page.querySelectorAll('[data-save]').forEach((btn) => {
        btn.addEventListener('click', async () => {
            const fieldName = btn.dataset.save;
            const field = page.querySelector(`.info-tile[data-field="${fieldName}"]`);
            if (!field) return;

            const errorEl = field.querySelector(`[data-error="${fieldName}"]`);
            if (errorEl) errorEl.textContent = '';

            btn.disabled = true;

            try {
                if (fieldName === 'password') {
                    const currentPassword = document.getElementById('currentPassword')?.value || '';
                    const password = document.getElementById('newPassword')?.value || '';
                    const passwordConfirmation = document.getElementById('newPasswordConfirm')?.value || '';

                    const response = await fetch(passwordUrl, {
                        method: 'PATCH',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': csrfToken,
                            'Accept': 'application/json',
                        },
                        body: JSON.stringify({
                            current_password: currentPassword,
                            password,
                            password_confirmation: passwordConfirmation,
                        }),
                    });

                    const data = await response.json();
                    if (!response.ok) {
                        const errMsg =
                            data?.errors?.current_password?.[0] ||
                            data?.errors?.password?.[0] ||
                            data?.message ||
                            'Не удалось изменить пароль';
                        if (errorEl) errorEl.textContent = errMsg;
                        return;
                    }

                    ['currentPassword', 'newPassword', 'newPasswordConfirm'].forEach((id) => {
                        const input = document.getElementById(id);
                        if (input) input.value = '';
                    });
                    closeEditor('password');
                    showToast(data.message || 'Пароль обновлён');
                    return;
                }

                const input = field.querySelector(`[data-editor="${fieldName}"] .field-input`);
                const value = input ? input.value.trim() : '';

                const response = await fetch(infoUrl, {
                    method: 'PATCH',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify({ [fieldName]: value }),
                });

                const data = await response.json();
                if (!response.ok) {
                    const errMsg = data?.errors?.[fieldName]?.[0] || data?.message || 'Ошибка сохранения';
                    if (errorEl) errorEl.textContent = errMsg;
                    return;
                }

                const display = field.querySelector(`[data-field-display="${fieldName}"]`);
                const updatedVal = data.user?.[fieldName] ?? '';
                if (display) {
                    if (updatedVal) {
                        display.textContent = updatedVal;
                        display.classList.remove('is-empty');
                    } else {
                        display.textContent = display.dataset.placeholder || 'Не указано';
                        display.classList.add('is-empty');
                    }
                }

                if (fieldName === 'username' && nicknameEl && updatedVal) {
                    nicknameEl.textContent = updatedVal;
                }

                if (fieldName === 'study_group' && groupBadgeEl) {
                    groupBadgeEl.textContent = updatedVal;
                    groupBadgeEl.classList.toggle('is-hidden', !updatedVal);
                }

                closeEditor(fieldName);
                showToast(data.message || 'Сохранено');
            } catch {
                if (errorEl) errorEl.textContent = 'Ошибка соединения с сервером';
            } finally {
                btn.disabled = false;
            }
        });
    });
});
