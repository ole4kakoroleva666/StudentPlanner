document.addEventListener('DOMContentLoaded', function() {
    // 1. Ищем элементы через querySelector
    const modal = document.querySelector('#featuresModal');
    const btn = document.querySelector('#learnMoreBtn');
    const closeBtn = document.querySelector('.close-btn');

    // Проверяем, что элементы существуют на странице
    if (!modal || !btn || !closeBtn) return;

    // 2. Открываем окно при клике на кнопку "Узнать больше"
    btn.addEventListener('click', function() {
        modal.style.display = 'block';
    });

    // 3. Закрываем окно при клике на крестик
    closeBtn.addEventListener('click', function() {
        modal.style.display = 'none';
    });

    // 4. Закрываем окно при клике на затемненный фон (оверлей)
    window.addEventListener('click', function(e) {
        if (e.target === modal) {
            modal.style.display = 'none';
        }
    });

    // 5. Закрываем окно при нажатии клавиши Escape
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape' && modal.style.display === 'block') {
            modal.style.display = 'none';
        }
    });
});