<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CalendarController;
use App\Http\Controllers\HomeworkController;
use App\Http\Controllers\NoteController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\PersonalFileController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ScheduleController;
use App\Http\Controllers\TaskController;

// Главная страница: через TaskController (выводим задачи или welcome для гостя)
Route::get('/', [TaskController::class, 'index'])->name('home');

Route::get('/welcome', function () {
    return view('welcome');
});

Route::get('/error', function () {
    return view('error');
});

// Авторизация, Регистрация и Сброс пароля
Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
Route::post('/register', [AuthController::class, 'register']);

Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login']);

Route::post('/forgot-password', [AuthController::class, 'sendResetLink'])->name('password.email');
Route::get('/reset-password/{token}', [AuthController::class, 'showResetPassword'])->name('password.reset');
Route::post('/reset-password', [AuthController::class, 'resetPassword'])->name('password.update');

Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// Маршруты приложения (доступны только авторизованным пользователям)
Route::middleware('auth')->group(function () {
    // Задачи
    Route::get('/task', [TaskController::class, 'taskPage'])->name('task');
    Route::post('/tasks', [TaskController::class, 'store'])->name('tasks.store');
    Route::patch('/tasks/{task}/toggle', [TaskController::class, 'toggle'])->name('tasks.toggle');
    Route::patch('/tasks/{task}/status', [TaskController::class, 'updateStatus'])->name('tasks.status');
    Route::delete('/tasks/{task}', [TaskController::class, 'destroy'])->name('tasks.destroy');

    // Заметки
    Route::get('/notes', [NoteController::class, 'index'])->name('notes.index');
    Route::post('/notes', [NoteController::class, 'store'])->name('notes.store');
    Route::match(['post', 'put', 'patch'], '/notes/{note}', [NoteController::class, 'update'])->name('notes.update');
    Route::patch('/notes/{note}/pin', [NoteController::class, 'togglePin'])->name('notes.pin');
    Route::delete('/notes/{note}', [NoteController::class, 'destroy'])->name('notes.destroy');

    // Личные файлы
    Route::get('/privateFiels', [PersonalFileController::class, 'index'])->name('private-files.index');
    Route::post('/privateFiels', [PersonalFileController::class, 'store'])->name('private-files.store');
    Route::get('/privateFiels/{file}/download', [PersonalFileController::class, 'download'])->name('private-files.download');
    Route::delete('/privateFiels/{file}', [PersonalFileController::class, 'destroy'])->name('private-files.destroy');

    // Профиль
    Route::get('/profile', [ProfileController::class, 'index'])->name('profile.index');
    Route::post('/profile/avatar', [ProfileController::class, 'updateAvatar'])->name('profile.avatar.update');
    Route::delete('/profile/avatar', [ProfileController::class, 'deleteAvatar'])->name('profile.avatar.delete');
    Route::patch('/profile/info', [ProfileController::class, 'updateInfo'])->name('profile.info.update');
    Route::patch('/profile/password', [ProfileController::class, 'updatePassword'])->name('profile.password.update');

    // Уведомления
    Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');

    // Домашнее задание
    Route::get('/homework', [HomeworkController::class, 'index'])->name('homework.index');
    Route::get('/homeworkNew', [HomeworkController::class, 'create'])->name('homework.create');
    Route::get('/homework/list', [HomeworkController::class, 'list'])->name('homework.list');
    Route::post('/homework', [HomeworkController::class, 'store'])->name('homework.store');
    Route::get('/homework/{homework}/download', [HomeworkController::class, 'download'])->name('homework.download');
    Route::delete('/homework/{homework}', [HomeworkController::class, 'destroy'])->name('homework.destroy');

    // Расписание
    Route::get('/shedule', [ScheduleController::class, 'index'])->name('schedule.index');
    Route::post('/shedule', [ScheduleController::class, 'store'])->name('schedule.store');
    Route::delete('/shedule/{lesson}', [ScheduleController::class, 'destroy'])->name('schedule.destroy');

    // Календарь и план на день
    Route::get('/calendar', [CalendarController::class, 'index'])->name('calendar.index');
    Route::get('/calendarOneDay', [CalendarController::class, 'oneDay'])->name('calendar.oneDay');
    Route::get('/calendar/events', [CalendarController::class, 'events'])->name('calendar.events');
    Route::get('/calendar/day', [CalendarController::class, 'dayData'])->name('calendar.day');
    Route::post('/calendar/day-notes', [CalendarController::class, 'storeDayNote'])->name('calendar.day-notes.store');
    Route::patch('/calendar/day-notes/{dayNote}/toggle', [CalendarController::class, 'toggleDayNote'])->name('calendar.day-notes.toggle');
    Route::delete('/calendar/day-notes/{dayNote}', [CalendarController::class, 'destroyDayNote'])->name('calendar.day-notes.destroy');
});
