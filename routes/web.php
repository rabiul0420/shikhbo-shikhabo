<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\AcademicStructureController;
use App\Http\Controllers\AdminDashboardController;
use App\Http\Controllers\AdminResultController;
use App\Http\Controllers\AdminSchoolController;
use App\Http\Controllers\AdminStudentController;
use App\Http\Controllers\ExamController;
use App\Http\Controllers\ExamAttemptController;
use App\Http\Controllers\QuestionController;
use App\Models\Exam;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    $exams = Exam::query()
        ->with(['academicClass', 'subject', 'chapter', 'questions'])
        ->latest()
        ->get();

    return view('welcome', compact('exams'));
})->name('home');

Route::view('/privacy-policy', 'pages.privacy-policy')->name('privacy-policy');
Route::view('/about-us', 'pages.about-us')->name('about-us');
Route::view('/contact-us', 'pages.contact-us')->name('contact-us');

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register']);
});

Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth')->name('logout');

Route::middleware(['auth', 'admin'])->group(function () {
    Route::get('/admin', [AdminDashboardController::class, 'index'])->name('admin.index');
    Route::get('/admin/questions/create', [QuestionController::class, 'create'])->name('admin.questions.create');
    Route::get('/admin/exams', [ExamController::class, 'index'])->name('admin.exams.index');
    Route::get('/admin/exams/{exam}/results', [AdminResultController::class, 'exam'])->name('admin.exams.results');
    Route::get('/admin/results', [AdminResultController::class, 'index'])->name('admin.results.index');
    Route::get('/admin/students', [AdminStudentController::class, 'index'])->name('admin.students.index');
    Route::get('/admin/schools', [AdminSchoolController::class, 'index'])->name('admin.schools.index');
    Route::post('/admin/schools', [AdminSchoolController::class, 'store'])->name('admin.schools.store');
    Route::patch('/admin/schools/{school}', [AdminSchoolController::class, 'update'])->name('admin.schools.update');
    Route::delete('/admin/schools/{school}', [AdminSchoolController::class, 'destroy'])->name('admin.schools.destroy');
    Route::redirect('/admin/academic', '/admin/academic/classes')->name('admin.academic.index');
    Route::get('/admin/academic/classes', [AcademicStructureController::class, 'classes'])->name('admin.academic.classes');
    Route::get('/admin/academic/subjects', [AcademicStructureController::class, 'subjects'])->name('admin.academic.subjects');
    Route::get('/admin/academic/chapters/options', [AcademicStructureController::class, 'chapterOptions'])->name('admin.academic.chapters.options');
    Route::get('/admin/academic/chapters', [AcademicStructureController::class, 'chapters'])->name('admin.academic.chapters');
    Route::post('/admin/classes', [AcademicStructureController::class, 'storeClass'])->name('admin.classes.store');
    Route::patch('/admin/classes/{academicClass}', [AcademicStructureController::class, 'updateClass'])->name('admin.classes.update');
    Route::delete('/admin/classes/{academicClass}', [AcademicStructureController::class, 'destroyClass'])->name('admin.classes.destroy');
    Route::post('/admin/subjects', [AcademicStructureController::class, 'storeSubject'])->name('admin.subjects.store');
    Route::patch('/admin/subjects/{subject}', [AcademicStructureController::class, 'updateSubject'])->name('admin.subjects.update');
    Route::delete('/admin/subjects/{subject}', [AcademicStructureController::class, 'destroySubject'])->name('admin.subjects.destroy');
    Route::post('/admin/chapters', [AcademicStructureController::class, 'storeChapter'])->name('admin.chapters.store');
    Route::patch('/admin/chapters/{chapter}', [AcademicStructureController::class, 'updateChapter'])->name('admin.chapters.update');
    Route::delete('/admin/chapters/{chapter}', [AcademicStructureController::class, 'destroyChapter'])->name('admin.chapters.destroy');

    Route::post('/questions', [QuestionController::class, 'storeStandalone'])->name('questions.standalone.store');
    Route::patch('/questions/{question}', [QuestionController::class, 'update'])->name('questions.update');
    Route::delete('/questions/{question}', [QuestionController::class, 'destroy'])->name('questions.destroy');
    Route::post('/exams', [ExamController::class, 'store'])->name('exams.store');
    Route::patch('/exams/{exam}', [ExamController::class, 'update'])->name('exams.update');
    Route::delete('/exams/{exam}', [ExamController::class, 'destroy'])->name('exams.destroy');
});

Route::middleware('auth')->group(function () {
    Route::view('/my-profile', 'profile.show')->name('profile.show');
    Route::get('/my-results', [ExamAttemptController::class, 'myResults'])->name('exam-attempts.index');
    Route::get('/exams/{exam}/results', [ExamAttemptController::class, 'allResults'])->name('exams.results');
    Route::get('/exams/{exam}', [ExamAttemptController::class, 'show'])->name('exams.show');
    Route::post('/exams/{exam}/submit', [ExamAttemptController::class, 'submit'])->name('exams.submit');
    Route::get('/exam-attempts/{attempt}', [ExamAttemptController::class, 'result'])->name('exam-attempts.result');
});
