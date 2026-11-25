/**
 * Показывает фирменное модальное окно подтверждения удаления.
 * @param {Object} options
 * @param {string} [options.title='Удалить элемент?']
 * @param {string} [options.message='Вы уверены, что хотите удалить это?']
 * @param {string} [options.confirmText='Удалить']
 * @param {string} [options.cancelText='Отмена']
 * @returns {Promise<boolean>}
 */
export function confirmDelete({
    title = 'Удалить элемент?',
    message = 'Вы уверены, что хотите удалить это?',
    confirmText = 'Удалить',
    cancelText = 'Отмена',
} = {}) {
    return new Promise((resolve) => {
        let backdrop = document.getElementById('spConfirmBackdrop');
        if (!backdrop) {
            backdrop = document.createElement('div');
            backdrop.id = 'spConfirmBackdrop';
            backdrop.className = 'sp-confirm-backdrop';
            backdrop.innerHTML = `
                <div class="sp-confirm-modal" role="dialog" aria-modal="true" aria-labelledby="spConfirmTitle">
                    <h3 class="sp-confirm-title" id="spConfirmTitle"></h3>
                    <p class="sp-confirm-message" id="spConfirmMessage"></p>
                    <div class="sp-confirm-actions">
                        <button type="button" class="sp-confirm-cancel" id="spConfirmCancel"></button>
                        <button type="button" class="sp-confirm-delete" id="spConfirmOk"></button>
                    </div>
                </div>
            `;
            document.body.appendChild(backdrop);
        }

        const titleEl = backdrop.querySelector('#spConfirmTitle');
        const msgEl = backdrop.querySelector('#spConfirmMessage');
        const cancelBtn = backdrop.querySelector('#spConfirmCancel');
        const okBtn = backdrop.querySelector('#spConfirmOk');

        titleEl.textContent = title;
        msgEl.textContent = message;
        cancelBtn.textContent = cancelText;
        okBtn.textContent = confirmText;

        backdrop.classList.add('is-open');
        okBtn.focus();

        function cleanup(result) {
            backdrop.classList.remove('is-open');
            cancelBtn.removeEventListener('click', onCancel);
            okBtn.removeEventListener('click', onConfirm);
            backdrop.removeEventListener('click', onBackdropClick);
            document.removeEventListener('keydown', onKeyDown);
            resolve(result);
        }

        function onCancel() {
            cleanup(false);
        }

        function onConfirm() {
            cleanup(true);
        }

        function onBackdropClick(e) {
            if (e.target === backdrop) {
                cleanup(false);
            }
        }

        function onKeyDown(e) {
            if (e.key === 'Escape') {
                cleanup(false);
            }
        }

        cancelBtn.addEventListener('click', onCancel);
        okBtn.addEventListener('click', onConfirm);
        backdrop.addEventListener('click', onBackdropClick);
        document.addEventListener('keydown', onKeyDown);
    });
}
