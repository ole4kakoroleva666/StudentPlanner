import { confirmDelete } from './confirmModal';

document.addEventListener('DOMContentLoaded', () => {
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';

    // Элементы создания заметки
    const creatorWrapper = document.getElementById('noteCreatorWrapper');
    const notesInputTrigger = document.getElementById('notesInputTrigger');
    const createForm = document.getElementById('noteCreateForm');
    const createTitleInput = document.getElementById('createNoteTitle');
    const createEditor = document.getElementById('createNoteEditor');
    const createPinBtn = document.getElementById('createPinBtn');
    const createIsPinnedInput = document.getElementById('createIsPinnedInput');
    const createToolbar = document.getElementById('createNoteToolbar');
    const createFileInput = document.getElementById('createNoteFileInput');
    const createAttachmentPreview = document.getElementById('createAttachmentPreview');
    const createAttachmentName = document.getElementById('createAttachmentName');
    const createAttachmentRemove = document.getElementById('createAttachmentRemove');
    const createSubmitBtn = document.getElementById('createSubmitBtn');

    // Сетки заметок
    const pinnedSection = document.getElementById('pinnedSection');
    const pinnedGrid = document.getElementById('pinnedNotesGrid');
    const otherGrid = document.getElementById('otherNotesGrid');
    const otherSectionLabel = document.getElementById('otherSectionLabel');
    const notesEmptyState = document.getElementById('notesEmptyState');

    // Модальное окно
    const modalBackdrop = document.getElementById('noteModalBackdrop');
    const modalView = document.getElementById('noteModalView');
    const modalViewTitle = document.getElementById('modalViewTitle');
    const modalViewBody = document.getElementById('modalViewBody');
    const modalViewPinBtn = document.getElementById('modalViewPinBtn');
    const modalViewAttachment = document.getElementById('modalViewAttachment');
    const modalViewAttachmentLink = document.getElementById('modalViewAttachmentLink');
    const modalViewAttachmentName = document.getElementById('modalViewAttachmentName');
    const modalDeleteBtn = document.getElementById('modalDeleteBtn');
    const modalEditBtn = document.getElementById('modalEditBtn');

    const modalEditForm = document.getElementById('noteModalEditForm');
    const editTitleInput = document.getElementById('editNoteTitle');
    const editEditor = document.getElementById('editNoteEditor');
    const editPinBtn = document.getElementById('editPinBtn');
    const editToolbar = document.getElementById('editNoteToolbar');
    const editFileInput = document.getElementById('editNoteFileInput');
    const editAttachmentPreview = document.getElementById('editAttachmentPreview');
    const editAttachmentName = document.getElementById('editAttachmentName');
    const editAttachmentRemove = document.getElementById('editAttachmentRemove');
    const editCancelBtn = document.getElementById('editCancelBtn');
    const editSaveBtn = document.getElementById('editSaveBtn');

    let createPinned = false;
    let activeCard = null;
    let editPinned = false;
    let editRemoveAttachment = false;

    const pinIconSrc = document.querySelector('#createPinBtn img')?.getAttribute('src') || '/media/pin.svg';

    // --- Вспомогательные функции для инлайн H1/H2 и сброса стиля ---
    function unwrapElement(el) {
        const parent = el.parentNode;
        if (!parent) return;
        while (el.firstChild) {
            parent.insertBefore(el.firstChild, el);
        }
        parent.removeChild(el);
    }

    function findClosestHeadingSpan(node, editorEl) {
        let current = node && node.nodeType === Node.TEXT_NODE ? node.parentElement : node;
        while (current && current !== editorEl) {
            if (current.tagName === 'SPAN' && (current.classList.contains('note-h1') || current.classList.contains('note-h2'))) {
                return current;
            }
            current = current.parentElement;
        }
        return null;
    }

    function toggleInlineHeading(editorEl, targetClass) {
        const selection = window.getSelection();
        if (!selection || selection.rangeCount === 0) return false;
        const range = selection.getRangeAt(0);
        if (!editorEl.contains(range.commonAncestorContainer)) return false;

        // Если ничего не выделено — форматируем текущий блок
        if (range.collapsed) {
            return false;
        }

        const existingSpan = findClosestHeadingSpan(range.commonAncestorContainer, editorEl);
        if (existingSpan && existingSpan.classList.contains(targetClass)) {
            unwrapElement(existingSpan);
            return true;
        }

        const fragment = range.extractContents();
        fragment.querySelectorAll('span.note-h1, span.note-h2').forEach((span) => unwrapElement(span));

        const span = document.createElement('span');
        span.className = targetClass;
        span.appendChild(fragment);
        range.insertNode(span);

        const newRange = document.createRange();
        newRange.selectNodeContents(span);
        selection.removeAllRanges();
        selection.addRange(newRange);
        return true;
    }

    // --- Переиспользуемая панель форматирования ---
    function initEditorToolbar(toolbarEl, editorEl, fileInputEl) {
        const buttons = toolbarEl.querySelectorAll('.tb-btn');
        let savedRange = null;

        function saveSelection() {
            const selection = window.getSelection();
            if (!selection || selection.rangeCount === 0) return;
            const range = selection.getRangeAt(0);
            if (editorEl.contains(range.commonAncestorContainer)) {
                savedRange = range.cloneRange();
            }
        }

        function restoreSelection() {
            if (!savedRange) return;
            const selection = window.getSelection();
            if (!selection) return;
            selection.removeAllRanges();
            selection.addRange(savedRange);
        }

        buttons.forEach((btn) => {
            // mousedown + preventDefault сохраняет выделение текста в contenteditable
            btn.addEventListener('mousedown', (e) => {
                e.preventDefault();
                saveSelection();
            });

            btn.addEventListener('click', () => {
                if (btn.dataset.action === 'attach') {
                    fileInputEl.click();
                    return;
                }

                const cmd = btn.dataset.cmd;
                editorEl.focus({ preventScroll: true });
                restoreSelection();

                if (cmd === 'h1' || cmd === 'h2') {
                    const targetClass = cmd === 'h1' ? 'note-h1' : 'note-h2';
                    toggleInlineHeading(editorEl, targetClass);
                    saveSelection();
                } else if (cmd === 'normal') {
                    const selection = window.getSelection();
                    if (selection && selection.rangeCount > 0) {
                        const range = selection.getRangeAt(0);
                        const headingSpan = findClosestHeadingSpan(range.commonAncestorContainer, editorEl);
                        if (headingSpan) {
                            unwrapElement(headingSpan);
                        }
                    }
                    document.execCommand('removeFormat', false, null);
                    document.execCommand('formatBlock', false, 'p');
                    saveSelection();
                } else if (cmd) {
                    document.execCommand(cmd, false, null);
                    saveSelection();
                }

                updateToolbarState();
            });
        });

        function updateToolbarState() {
            if (document.activeElement !== editorEl) {
                buttons.forEach((btn) => btn.classList.remove('active'));
                return;
            }
            const currentBlock = (document.queryCommandValue('formatBlock') || '').toLowerCase();
            const selection = window.getSelection();
            const activeSpan = selection && selection.rangeCount > 0
                ? findClosestHeadingSpan(selection.getRangeAt(0).commonAncestorContainer, editorEl)
                : null;

            buttons.forEach((btn) => {
                const cmd = btn.dataset.cmd;
                if (!cmd || cmd === 'undo' || cmd === 'redo' || cmd === 'normal') {
                    return;
                }
                if (cmd === 'h1' || cmd === 'h2') {
                    const spanMatch = activeSpan && activeSpan.classList.contains(cmd === 'h1' ? 'note-h1' : 'note-h2');
                    btn.classList.toggle('active', currentBlock === cmd || Boolean(spanMatch));
                } else {
                    try {
                        btn.classList.toggle('active', document.queryCommandState(cmd));
                    } catch {
                        btn.classList.remove('active');
                    }
                }
            });
        }

        editorEl.addEventListener('keyup', () => { saveSelection(); updateToolbarState(); });
        editorEl.addEventListener('mouseup', () => { saveSelection(); updateToolbarState(); });
        editorEl.addEventListener('focus', updateToolbarState);
        editorEl.addEventListener('blur', () => {
            setTimeout(updateToolbarState, 0);
        });
        document.addEventListener('selectionchange', () => {
            if (document.activeElement === editorEl) {
                saveSelection();
                updateToolbarState();
            }
        });
    }

    initEditorToolbar(createToolbar, createEditor, createFileInput);
    initEditorToolbar(editToolbar, editEditor, editFileInput);

    // --- Управление формой создания ---
    function openCreateForm() {
        notesInputTrigger.classList.add('hidden');
        createForm.classList.remove('hidden');
        createEditor.focus();
    }

    function resetCreateForm() {
        createTitleInput.value = '';
        createEditor.innerHTML = '';
        createPinned = false;
        if (createIsPinnedInput) createIsPinnedInput.value = '0';
        createPinBtn.classList.remove('pinned');
        createPinBtn.setAttribute('aria-pressed', 'false');
        createFileInput.value = '';
        createAttachmentPreview.classList.add('hidden');
        createAttachmentName.textContent = '';
        createForm.classList.add('hidden');
        notesInputTrigger.classList.remove('hidden');
    }

    notesInputTrigger.addEventListener('click', openCreateForm);
    notesInputTrigger.addEventListener('keydown', (e) => {
        if (e.key === 'Enter' || e.key === ' ') {
            e.preventDefault();
            openCreateForm();
        }
    });

    // Сворачиваем пустую форму при клике вне неё
    document.addEventListener('click', (e) => {
        if (!createForm.classList.contains('hidden') && !creatorWrapper.contains(e.target)) {
            const hasContent =
                createTitleInput.value.trim() !== '' ||
                createEditor.innerText.trim() !== '' ||
                createFileInput.files.length > 0;
            if (!hasContent) {
                resetCreateForm();
            }
        }
    });

    createPinBtn.addEventListener('click', (e) => {
        e.preventDefault();
        e.stopPropagation();
        createPinned = !createPinned;
        if (createIsPinnedInput) {
            createIsPinnedInput.value = createPinned ? '1' : '0';
        }
        createPinBtn.classList.toggle('pinned', createPinned);
        createPinBtn.setAttribute('aria-pressed', createPinned ? 'true' : 'false');
    });

    createFileInput.addEventListener('change', () => {
        const file = createFileInput.files[0];
        if (file) {
            createAttachmentName.textContent = file.name;
            createAttachmentPreview.classList.remove('hidden');
        } else {
            createAttachmentPreview.classList.add('hidden');
        }
    });

    createAttachmentRemove.addEventListener('click', () => {
        createFileInput.value = '';
        createAttachmentName.textContent = '';
        createAttachmentPreview.classList.add('hidden');
    });

    createForm.addEventListener('submit', async (e) => {
        e.preventDefault();

        const title = createTitleInput.value.trim();
        const content = createEditor.innerHTML.trim();
        const plainText = createEditor.innerText.trim();
        const file = createFileInput.files[0];

        if (!title && !plainText && !file) {
            createEditor.focus();
            return;
        }

        const formData = new FormData();
        formData.append('title', title);
        formData.append('content', content);
        formData.append('is_pinned', createPinned ? '1' : '0');
        if (file) {
            formData.append('attachment', file);
        }

        createSubmitBtn.disabled = true;
        try {
            const response = await fetch('/notes', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json',
                },
                body: formData,
            });

            const data = await response.json();
            if (!response.ok || !data.success) {
                alert(data.message || 'Не удалось сохранить заметку');
                return;
            }

            const card = createNoteCardElement(data.note);
            placeCardInGrid(card, data.note.is_pinned, true);
            syncSectionVisibility();
            resetCreateForm();
        } catch (err) {
            console.error(err);
            alert('Ошибка при сохранении заметки');
        } finally {
            createSubmitBtn.disabled = false;
        }
    });

    // --- Создание и обновление DOM-карточек ---
    function createNoteCardElement(note) {
        const card = document.createElement('div');
        card.className = 'one-notes';
        updateCardElement(card, note);
        return card;
    }

    function updateCardElement(card, note) {
        card.dataset.id = note.id;
        card.dataset.title = note.title || '';
        card.dataset.content = note.content || '';
        card.dataset.pinned = note.is_pinned ? '1' : '0';
        card.dataset.attachmentUrl = note.attachment_url || '';
        card.dataset.attachmentName = note.attachment_name || '';

        const displayTitle = note.title || 'Без названия';
        const attachmentHtml = note.attachment_name
            ? `<div class="note-card-attachment"><span>${escapeHtml(note.attachment_name)}</span></div>`
            : '';

        card.innerHTML = `
            <div class="name">
                <p>${escapeHtml(displayTitle)}</p>
                <button type="button" class="card-pin-btn ${note.is_pinned ? 'pinned' : ''}" title="${note.is_pinned ? 'Открепить' : 'Закрепить'}">
                    <img src="${pinIconSrc}" alt="pin">
                </button>
            </div>
            <div class="note-card-body">${note.content || ''}</div>
            ${attachmentHtml}
        `;
    }

    function placeCardInGrid(card, isPinned, prepend = false) {
        const targetGrid = isPinned ? pinnedGrid : otherGrid;
        if (prepend && targetGrid.firstChild) {
            targetGrid.insertBefore(card, targetGrid.firstChild);
        } else {
            targetGrid.appendChild(card);
        }
    }

    function syncSectionVisibility() {
        const pinnedCount = pinnedGrid.querySelectorAll('.one-notes').length;
        const otherCount = otherGrid.querySelectorAll('.one-notes').length;

        pinnedSection.classList.toggle('hidden', pinnedCount === 0);
        otherSectionLabel.classList.toggle('hidden', pinnedCount === 0 && otherCount === 0);
        notesEmptyState.classList.toggle('hidden', pinnedCount > 0 || otherCount > 0);
    }

    // --- Клики по карточкам (открытие модалки или быстрое закрепление) ---
    document.addEventListener('click', (e) => {
        const pinBtn = e.target.closest('.card-pin-btn');
        if (pinBtn) {
            e.stopPropagation();
            const card = pinBtn.closest('.one-notes');
            if (card) {
                togglePinForCard(card);
            }
            return;
        }

        const card = e.target.closest('.one-notes');
        if (card) {
            openNoteModal(card);
        }
    });

    async function togglePinForCard(card) {
        const noteId = card.dataset.id;
        try {
            const response = await fetch(`/notes/${noteId}/pin`, {
                method: 'PATCH',
                headers: {
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json',
                },
            });
            const data = await response.json();
            if (!response.ok || !data.success) {
                return;
            }

            updateCardElement(card, data.note);
            placeCardInGrid(card, data.note.is_pinned, true);
            syncSectionVisibility();

            if (activeCard === card) {
                modalViewPinBtn.classList.toggle('pinned', data.note.is_pinned);
                editPinned = data.note.is_pinned;
                editPinBtn.classList.toggle('pinned', editPinned);
            }
        } catch (err) {
            console.error(err);
        }
    }

    // --- Модальное окно просмотра и редактирования ---
    function openNoteModal(card) {
        activeCard = card;
        const title = card.dataset.title || '';
        const content = card.dataset.content || '';
        const isPinned = card.dataset.pinned === '1';
        const attachmentUrl = card.dataset.attachmentUrl || '';
        const attachmentName = card.dataset.attachmentName || '';

        modalViewTitle.textContent = title || 'Без названия';
        modalViewBody.innerHTML = content || '<span style="color:#9AA0C3">Пустая заметка</span>';
        modalViewPinBtn.classList.toggle('pinned', isPinned);

        if (attachmentUrl && attachmentName) {
            modalViewAttachmentLink.href = attachmentUrl;
            modalViewAttachmentName.textContent = attachmentName;
            modalViewAttachment.classList.remove('hidden');
        } else {
            modalViewAttachment.classList.add('hidden');
        }

        modalView.classList.remove('hidden');
        modalEditForm.classList.add('hidden');
        modalBackdrop.classList.remove('hidden');
        modalBackdrop.setAttribute('aria-hidden', 'false');
    }

    function closeNoteModal() {
        modalBackdrop.classList.add('hidden');
        modalBackdrop.setAttribute('aria-hidden', 'true');
        activeCard = null;
    }

    modalBackdrop.addEventListener('click', (e) => {
        if (e.target === modalBackdrop) {
            closeNoteModal();
        }
    });

    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape' && !modalBackdrop.classList.contains('hidden')) {
            closeNoteModal();
        }
    });

    modalViewPinBtn.addEventListener('click', () => {
        if (activeCard) {
            togglePinForCard(activeCard);
        }
    });

    modalEditBtn.addEventListener('click', () => {
        if (!activeCard) return;

        editTitleInput.value = activeCard.dataset.title || '';
        editEditor.innerHTML = activeCard.dataset.content || '';
        editPinned = activeCard.dataset.pinned === '1';
        editPinBtn.classList.toggle('pinned', editPinned);
        editFileInput.value = '';
        editRemoveAttachment = false;

        const existingAttachment = activeCard.dataset.attachmentName || '';
        if (existingAttachment) {
            editAttachmentName.textContent = existingAttachment;
            editAttachmentPreview.classList.remove('hidden');
        } else {
            editAttachmentPreview.classList.add('hidden');
        }

        modalView.classList.add('hidden');
        modalEditForm.classList.remove('hidden');
        editEditor.focus();
    });

    editPinBtn.addEventListener('click', () => {
        editPinned = !editPinned;
        editPinBtn.classList.toggle('pinned', editPinned);
    });

    editFileInput.addEventListener('change', () => {
        const file = editFileInput.files[0];
        if (file) {
            editAttachmentName.textContent = file.name;
            editAttachmentPreview.classList.remove('hidden');
            editRemoveAttachment = false;
        }
    });

    editAttachmentRemove.addEventListener('click', () => {
        editFileInput.value = '';
        editAttachmentName.textContent = '';
        editAttachmentPreview.classList.add('hidden');
        editRemoveAttachment = true;
    });

    editCancelBtn.addEventListener('click', () => {
        if (activeCard) {
            openNoteModal(activeCard);
        } else {
            closeNoteModal();
        }
    });

    modalEditForm.addEventListener('submit', async (e) => {
        e.preventDefault();
        if (!activeCard) return;

        const noteId = activeCard.dataset.id;
        const formData = new FormData();
        formData.append('title', editTitleInput.value.trim());
        formData.append('content', editEditor.innerHTML.trim());
        formData.append('is_pinned', editPinned ? '1' : '0');
        formData.append('remove_attachment', editRemoveAttachment ? '1' : '0');
        if (editFileInput.files[0]) {
            formData.append('attachment', editFileInput.files[0]);
        }

        editSaveBtn.disabled = true;
        try {
            const response = await fetch(`/notes/${noteId}`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json',
                },
                body: formData,
            });
            const data = await response.json();
            if (!response.ok || !data.success) {
                alert(data.message || 'Не удалось обновить заметку');
                return;
            }

            const wasPinned = activeCard.dataset.pinned === '1';
            updateCardElement(activeCard, data.note);
            if (wasPinned !== data.note.is_pinned) {
                placeCardInGrid(activeCard, data.note.is_pinned, true);
                syncSectionVisibility();
            }

            openNoteModal(activeCard);
        } catch (err) {
            console.error(err);
            alert('Ошибка при обновлении заметки');
        } finally {
            editSaveBtn.disabled = false;
        }
    });

    modalDeleteBtn.addEventListener('click', async () => {
        if (!activeCard) return;
        const confirmed = await confirmDelete({
            title: 'Удалить заметку?',
            message: 'Вы уверены, что хотите удалить эту заметку?'
        });
        if (!confirmed) return;

        const noteId = activeCard.dataset.id;
        try {
            const response = await fetch(`/notes/${noteId}`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json',
                },
            });
            const data = await response.json();
            if (!response.ok || !data.success) {
                alert('Не удалось удалить заметку');
                return;
            }

            activeCard.remove();
            syncSectionVisibility();
            closeNoteModal();
        } catch (err) {
            console.error(err);
            alert('Ошибка при удалении заметки');
        }
    });

    function escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }
});
