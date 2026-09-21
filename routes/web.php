<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\QuizApiController;

// Halaman Kuis Siswa
Route::get('/quiz', function () {
    return view('quiz');
});

// Halaman & Aksi Admin (TAMBAHKAN DUA BARIS INI)
Route::get('/admin', [QuizApiController::class, 'adminDashboard']);
Route::post('/admin/questions', [QuizApiController::class, 'storeQuestion']);
Route::get('/admin/questions/{id}/edit', [QuizApiController::class, 'editQuestion']);
Route::put('/admin/quizzes/{id}/settings', [QuizApiController::class, 'updateQuizSettings']);
Route::put('/admin/questions/{id}', [QuizApiController::class, 'updateQuestion']);

// Endpoint API
Route::prefix('api/v1')->group(function () {
    Route::post('/quizzes/{id}/start', [QuizApiController::class, 'startQuiz']);
    Route::post('/attempts/{attempt_id}/save-answer', [QuizApiController::class, 'saveAnswer']);
    Route::post('/attempts/{attempt_id}/submit', [QuizApiController::class, 'submitQuiz']);
});