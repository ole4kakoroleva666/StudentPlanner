<!DOCTYPE html>
<html lang="ru">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>StudentPlanner</title>
    <link rel="stylesheet" href="../../public/css/calendarOneDay.css">
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
                </div>
                <div class="week-mont">
                    <div class="month">
                        <p>20 сентября</p>
                    </div>
                    <div class="week">
                        <p>Суббота</p>
                    </div>
                </div>

                <div class="main-calendarOneDay">
                    <div class="shedule-calendarOneDay">
                        <div class="shedule-header">
                            <p>Расписание</p>
                            <img src="../../public/media/lessonNew.svg" alt="lesson">
                        </div>

                        <div class="onelesson">
                            <div class="day-header">
                                <h3>Понедельник</h3>
                                <p class="date">20 февраля</p>
                            </div>
                            <div class="time">
                                <img class="icons" src="../../public/media/time.svg" alt="time" />
                                <p>Время: 14:15 — 15:50</p>
                            </div>

                            <div class="subject">
                                <img class="icons" src="../../public/media/books.svg" alt="subject" />
                                <p>Предмет: Проектирование и разработка интерфейсов</p>
                            </div>

                            <div class="classroom">
                                <img class="icons" src="../../public/media/chool.svg" alt="classroom" />
                                <p>Аудитория: 1-306</p>
                            </div>

                            <div class="teacher">
                                <img class="icons" src="../../public/media/teacher.svg" alt="teacher" />
                                <p>Преподаватель: Гунько Григорий Александрович</p>
                            </div>
                        </div>

                        <div class="onelesson">
                            <div class="time">
                                <img class="icons" src="../../public/media/time.svg" alt="time" />
                                <p>Время: 14:15 — 15:50</p>
                            </div>

                            <div class="subject">
                                <img class="icons" src="../../public/media/books.svg" alt="subject" />
                                <p>Предмет: Проектирование и разработка интерфейсов</p>
                            </div>

                            <div class="classroom">
                                <img class="icons" src="../../public/media/chool.svg" alt="classroom" />
                                <p>Аудитория: 1-306</p>
                            </div>

                            <div class="teacher">
                                <img class="icons" src="../../public/media/teacher.svg" alt="teacher" />
                                <p>Преподаватель: Гунько Григорий Александрович</p>
                            </div>
                        </div>

                        <div class="onelesson">
                            <div class="time">
                                <img class="icons" src="../../public/media/time.svg" alt="time" />
                                <p>Время: 14:15 — 15:50</p>
                            </div>

                            <div class="subject">
                                <img class="icons" src="../../public/media/books.svg" alt="subject" />
                                <p>Предмет: Проектирование и разработка интерфейсов</p>
                            </div>

                            <div class="classroom">
                                <img class="icons" src="../../public/media/chool.svg" alt="classroom" />
                                <p>Аудитория: 1-306</p>
                            </div>

                            <div class="teacher">
                                <img class="icons" src="../../public/media/teacher.svg" alt="teacher" />
                                <p>Преподаватель: Гунько Григорий Александрович</p>
                            </div>
                        </div>
                    </div>

                    <div class="right-column">
                        <div class="homeworkblock">
                            <div class="homework-header">
                                <p>Домашнее задание</p>
                                <img src="../../public/media/ex.svg" alt="homework">
                            </div>

                            <div class="homework-tasks">
                                <div class="homework-task">
                                    <input type="checkbox" id="hw1">
                                    <label for="hw1">Решить 2 уравнения</label>
                                </div>

                                <div class="homework-task">
                                    <input type="checkbox" id="hw2">
                                    <label for="hw2">Написать доклад (тема: экология)</label>
                                </div>

                                <div class="homework-task">
                                    <input type="checkbox" id="hw3">
                                    <label for="hw3">Доделать лабу</label>
                                </div>
                            </div>

                            <button class="add-homework-btn">
                                <img src="../media/plus.svg" alt="add">
                                <span>Добавить задание</span>
                            </button>
                        </div>

                        <div class="notesblock">
                            <div class="notes-header">
                                <p>Задачи</p>
                            </div>

                            <div class="notes-list">
                                <div class="note-task">
                                    <input type="checkbox" id="note1">
                                    <label for="note1">Принести тетрадь по математике</label>
                                </div>

                                <div class="note-task">
                                    <input type="checkbox" id="note2">
                                    <label for="note2">Принести ручку</label>
                                </div>
                            </div>

                            <button class="add-note-btn">
                                <img src="../../public/media/plus.svg" alt="add">
                                <span>Добавить задачу</span>
                            </button>
                        </div>
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
                    <p>“StudentPlanner” — это онлайн-органайзер для учебы,
                        в котором ты сможешь хранить всё самое важное в одном месте</p>
                </div>

                <div class="footer-contact">
                    <h4>Контакты</h4>
                    <p>Email: support@studentplanner.app</p>
                    <p>Версия: 1.0.0</p>

                    <div class="footer-social">
                        <h4>Мы в соцсетях</h4>
                        <div class="social-icons">
                            <img src="../../public/media/goglenew.svg" alt="google">
                            <img src="../../public/media/telegram.svg" alt="telegram">
                            <img src="../../public/media/vk.svg" alt="vk">
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