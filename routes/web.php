<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\TaskController;

// Главная страница: через TaskController (выводим задачи или welcome для гостя)
Route::get('/', [TaskController::class, 'index'])->name('home');

Route::get('/welcome', function () {
    return view('welcome');
});

// Страницы приложения
Route::get('/calendar', function () {
    return view('calendar');
});

Route::get('/calendarOneDay', function () {
    return view('calendarOneDay');
});

Route::get('/error', function () {
    return view('error');
});

Route::get('/homework', function () {
    return view('homework');
});

Route::get('/homeworkNew', function () {
    return view('homeworkNew');
});

Route::get('/notes', function () {
    return view('notes');
});

Route::get('/privateFiels', function () {
    return view('privateFiels');
});

Route::get('/profile', function () {
    return view('profile');
});

Route::get('/shedule', function () {
    return view('shedule');
});

// Авторизация и Регистрация
Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
Route::post('/register', [AuthController::class, 'register']);

Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login']);

Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// Маршруты для задач (доступны только авторизованным пользователям)
Route::middleware('auth')->group(function () {
    Route::get('/task', [TaskController::class, 'taskPage'])->name('task');
    Route::post('/tasks', [TaskController::class, 'store'])->name('tasks.store');
    Route::patch('/tasks/{task}/toggle', [TaskController::class, 'toggle'])->name('tasks.toggle');
    Route::delete('/tasks/{task}', [TaskController::class, 'destroy'])->name('tasks.destroy');
});
