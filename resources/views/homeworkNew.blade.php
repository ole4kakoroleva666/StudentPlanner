<!DOCTYPE html>
<html lang="ru">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>StudentPlanner</title>
    <link rel="stylesheet" href="../../public/css/homeworkNew.css">
</head>

<body>
    <header>
        <nav>
            <img class="logotip" src="../../public/media/newlogo.svg" alt="Логотип">
            <a>Главная</a>
            <a>Задачи</a>
            <a>Календарь</a>
            <a>Домашнее задание</a>
            <a>Расписание</a>
            <a>Заметки</a>
            <a>Моё</a>
            <img src="../../public/media/Profile.svg" alt="Профиль">
        </nav>
    </header>

    <main>
        <section>
            <div class="container">
                <div class="title">
                    <img src="../../public/media/back.svg" alt="back">
                    <p>Добавить домашнее задание</p>
                </div>

                <div class="create-homework">

                    <div class="one-section">
                        <p>Предмет</p>
                        <div class="new-homework-input">
                            <input type="text" placeholder="">
                        </div>
                    </div>

                    <div class="one-section">
                        <p>ДЗ</p>
                        <div class="new-homework-input">
                            <input type="text" placeholder="">
                        </div>
                    </div>

                    <div class="one-section">
                        <p>Срок сдачи</p>
                        <div class="new-homework-input">
                            <input type="text" placeholder="">
                            <img src="../../public/media/calendar.svg" alt="calendar">
                        </div>
                    </div>
                </div>
                <div class="upload-file">
                    <span class="upload-file-text">Загрузить файл</span>
                    <div class="upload-file-input">
                        <input type="file" id="file-upload" class="file-input-hidden">
                        <label for="file-upload" class="upload-file-button">
                            Обзор
                        </label>
                        <img src="../../public/media/file.svg" alt="upload">
                    </div>
                </div>
                <div class="create-button">
                    <button class="add-task-button">Добавить</button>
                </div>

            </div>

            </div>
        </section>

        <footer>
            <div class="footer-container">
                <div class="footer-left">
                    <img class="footer-logo" src="../../public/media/newlogo.svg" alt="Логотип">
                    <p class="footer-desc">Твой личный помощник в обучении</p>
                </div>

                <div class="footer-info">
                    <h4>О нас</h4>
                    <p>“StudentPlanner” — это онлайн-органайзер для учебы, в котором ты сможешь хранить всё самое важное
                        в одном месте</p>
                </div>

                <div class="footer-contact">
                    <h4>Контакты</h4>
                    <p>Email: support@studentplanner.app</p>
                    <p>Версия: 1.0.0</p>

                    <div class="footer-social">
                        <h4>Мы в соцсетях</h4>
                        <div class="social-icons">
                            <img src="../../public/media/goglenew.svg" alt="google">
                            <img src="../../public/media/telegram.svg" alt="Telegram">
                            <img src="../../public/media/vk.svg" alt="VK">
                        </div>
                    </div>
                </div>
            </div>

            <div class="footer-bottom">
                <p>© 2025 StudentPlanner. Все права защищены.</p>
            </div>
        </footer>

    </main>
</body>

</html>