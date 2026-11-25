import { confirmDelete } from './confirmModal';

document.addEventListener('DOMContentLoaded', () => {
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
    const filesList = document.getElementById('filesList');
    const fileInput = document.getElementById('file-upload');
    const fileUploadLabel = document.getElementById('fileUploadLabel');
    const uploadDropzone = document.getElementById('uploadDropzone');
    const uploadStatus = document.getElementById('uploadFileStatus');

    if (!filesList || !fileInput) {
        return;
    }

    const uploadUrl = filesList.dataset.uploadUrl;
    const downloadIcon = filesList.dataset.downloadIcon;
    const deleteIcon = filesList.dataset.deleteIcon;
    const fileIcon = filesList.dataset.fileIcon;
    const currentPage = Number(filesList.dataset.currentPage || 1);
    const perPage = Number(filesList.dataset.perPage || 10);

    let statusTimer = null;

    function setStatus(message, type = '') {
        if (!uploadStatus) return;
        clearTimeout(statusTimer);
        uploadStatus.textContent = message;
        uploadStatus.className = 'upload-file-status';
        if (type) {
            uploadStatus.classList.add(`is-${type}`);
        }
        if (type === 'success' || type === 'error') {
            statusTimer = setTimeout(() => {
                uploadStatus.textContent = '';
                uploadStatus.className = 'upload-file-status';
            }, 4000);
        }
    }

    function formatSize(bytes) {
        const size = Number(bytes) || 0;
        if (size < 1024) return `${size} Б`;
        if (size < 1048576) return `${(size / 1024).toFixed(1).replace(/\.0$/, '')} КБ`;
        return `${(size / 1048576).toFixed(1).replace(/\.0$/, '')} МБ`;
    }

    function ensureEmptyState() {
        const hasRows = filesList.querySelector('.file-row') !== null;
        let emptyEl = document.getElementById('filesEmptyState');

        if (!hasRows && !emptyEl) {
            emptyEl = document.createElement('div');
            emptyEl.id = 'filesEmptyState';
            emptyEl.className = 'files-empty-state';
            emptyEl.innerHTML = `
                <img src="${fileIcon}" alt="" class="files-empty-icon">
                <p>У вас пока нет загруженных файлов</p>
                <span>Нажмите на папку выше, чтобы добавить первый документ</span>
            `;
            filesList.appendChild(emptyEl);
        } else if (hasRows && emptyEl) {
            emptyEl.remove();
        }
    }

    function createFileRow(file) {
        const row = document.createElement('div');
        row.className = 'file-row';
        row.dataset.fileId = String(file.id);
        row.dataset.fileName = file.original_name;

        const inner = document.createElement('div');
        inner.className = 'one-private-fiels';

        const iconWrap = document.createElement('div');
        iconWrap.className = 'private-fiels-icon';
        const iconImg = document.createElement('img');
        iconImg.src = fileIcon;
        iconImg.alt = 'Файл';
        iconWrap.appendChild(iconImg);

        const meta = document.createElement('div');
        meta.className = 'private-fiels-meta';

        const nameSpan = document.createElement('span');
        nameSpan.className = 'private-fiels-name';
        nameSpan.title = file.original_name;
        nameSpan.textContent = file.original_name;

        const details = document.createElement('span');
        details.className = 'private-fiels-details';
        details.innerHTML = `<span>${file.created_at_formatted}</span><span class="dot-sep">•</span><span>${formatSize(file.size)}</span>`;

        meta.append(nameSpan, details);

        const buttons = document.createElement('div');
        buttons.className = 'private-fiels-buttons';

        const downloadLink = document.createElement('a');
        downloadLink.href = file.download_url;
        downloadLink.className = 'private-fiels-download';
        downloadLink.title = 'Скачать';
        const dlImg = document.createElement('img');
        dlImg.src = downloadIcon;
        dlImg.alt = 'Скачать';
        downloadLink.appendChild(dlImg);

        const deleteBtn = document.createElement('button');
        deleteBtn.type = 'button';
        deleteBtn.className = 'private-fiels-delete';
        deleteBtn.dataset.deleteUrl = file.delete_url;
        deleteBtn.title = 'Удалить';
        const delImg = document.createElement('img');
        delImg.src = deleteIcon;
        delImg.alt = 'Удалить';
        deleteBtn.appendChild(delImg);

        buttons.append(downloadLink, deleteBtn);
        inner.append(iconWrap, meta, buttons);
        row.appendChild(inner);

        return row;
    }

    async function uploadSingleFile(file) {
        const formData = new FormData();
        formData.append('file', file);

        const response = await fetch(uploadUrl, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json',
            },
            body: formData,
        });

        const data = await response.json().catch(() => ({}));

        if (!response.ok || !data.success) {
            const firstError = data?.errors?.file?.[0] || data?.message || 'Не удалось загрузить файл.';
            throw new Error(firstError);
        }

        return data.file;
    }

    async function handleFiles(fileList) {
        const selectedFiles = Array.from(fileList || []);
        if (selectedFiles.length === 0) return;

        fileInput.disabled = true;
        fileUploadLabel?.classList.add('is-disabled');

        let uploadedCount = 0;
        let lastError = null;

        for (let i = 0; i < selectedFiles.length; i++) {
            const file = selectedFiles[i];
            setStatus(
                selectedFiles.length > 1
                    ? `Загрузка ${i + 1} из ${selectedFiles.length}...`
                    : 'Загрузка...'
            );

            try {
                const createdFile = await uploadSingleFile(file);
                const row = createFileRow(createdFile);
                filesList.prepend(row);
                ensureEmptyState();
                uploadedCount++;
            } catch (err) {
                lastError = err.message;
            }
        }

        fileInput.value = '';
        fileInput.disabled = false;
        fileUploadLabel?.classList.remove('is-disabled');

        if (lastError) {
            setStatus(lastError, 'error');
        } else if (uploadedCount > 0) {
            const totalRowsNow = filesList.querySelectorAll('.file-row').length;
            if (currentPage > 1 || totalRowsNow > perPage) {
                window.location.href = `${window.location.pathname}?page=1`;
                return;
            }
            setStatus(
                uploadedCount > 1 ? `Загружено файлов: ${uploadedCount}` : 'Файл загружен',
                'success'
            );
        }
    }

    fileInput.addEventListener('change', () => {
        handleFiles(fileInput.files);
    });

    if (uploadDropzone) {
        ['dragenter', 'dragover'].forEach((evtName) => {
            uploadDropzone.addEventListener(evtName, (e) => {
                e.preventDefault();
                e.stopPropagation();
                uploadDropzone.classList.add('is-dragover');
            });
        });

        ['dragleave', 'drop'].forEach((evtName) => {
            uploadDropzone.addEventListener(evtName, (e) => {
                e.preventDefault();
                e.stopPropagation();
                uploadDropzone.classList.remove('is-dragover');
            });
        });

        uploadDropzone.addEventListener('drop', (e) => {
            if (e.dataTransfer?.files?.length) {
                handleFiles(e.dataTransfer.files);
            }
        });
    }

    filesList.addEventListener('click', async (event) => {
        const deleteBtn = event.target.closest('.private-fiels-delete');
        if (!deleteBtn) return;

        const row = deleteBtn.closest('.file-row');
        const fileName = row?.dataset.fileName || 'этот файл';
        const deleteUrl = deleteBtn.dataset.deleteUrl;
        if (!row || !deleteUrl) return;

        const confirmed = await confirmDelete({
            title: 'Удалить файл?',
            message: `Вы уверены, что хотите удалить «${fileName}»?`,
            confirmText: 'Удалить',
            cancelText: 'Отмена',
        });

        if (!confirmed) return;

        deleteBtn.disabled = true;

        try {
            const response = await fetch(deleteUrl, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json',
                },
            });

            if (!response.ok) {
                throw new Error('Ошибка при удалении файла.');
            }

            row.remove();

            const remainingRows = filesList.querySelectorAll('.file-row').length;
            if (remainingRows === 0 && currentPage > 1) {
                window.location.href = `${window.location.pathname}?page=${currentPage - 1}`;
                return;
            }

            ensureEmptyState();
            setStatus('Файл удалён', 'success');
        } catch (err) {
            deleteBtn.disabled = false;
            setStatus(err.message || 'Не удалось удалить файл.', 'error');
        }
    });
});
