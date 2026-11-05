import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';

export default defineConfig({
    plugins: [
        laravel({
            input: [
                // Все ваши CSS-файлы со скриншота:
                'resources/css/base.css',
                'resources/css/style.css',
                'resources/css/calendar.css',
                'resources/css/calendarOneDay.css',
                'resources/css/homework.css',
                'resources/css/homeworkNew.css',
                'resources/css/notes.css',
                'resources/css/privateFiels.css',
                'resources/css/profile.css',
                'resources/css/shedule.css',
                'resources/css/task.css',
                'resources/css/welcome.css',
                'resources/css/login.css',
                'resources/css/register.css',
                'resources/css/error.css',

                // Ваши JS-скрипты под каждую страницу:
                'resources/js/index.js',
                'resources/js/task.js',
                'resources/js/notes.js',
                'resources/js/privateFiels.js',
                'resources/js/homework.js',
                'resources/js/homeworkNew.js',
                'resources/js/profile.js',
                'resources/js/calendar.js',
                'resources/js/welcome.js',
            ],
            refresh: true, // горячая перезагрузка браузера
        }),
    ],
});