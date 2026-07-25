<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\AcademicStructureController;
use App\Http\Controllers\AdminCustomResultController;
use App\Http\Controllers\AdminDashboardController;
use App\Http\Controllers\AdminGiftRecipientController;
use App\Http\Controllers\AdminResultController;
use App\Http\Controllers\AdminSchoolController;
use App\Http\Controllers\AdminStudentController;
use App\Http\Controllers\AdminUserController;
use App\Http\Controllers\ClassExamController;
use App\Http\Controllers\CustomExamController;
use App\Http\Controllers\ExamController;
use App\Http\Controllers\ExamAttemptController;
use App\Http\Controllers\QuestionController;
use App\Models\AcademicClass;
use App\Models\Exam;
use App\Models\GiftAward;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    $examRelations = ['academicClass', 'subject', 'chapter', 'questions'];

    if (auth()->check() && ! auth()->user()->is_admin) {
        $examRelations['attempts'] = fn ($query) => $query
            ->where('user_id', auth()->id())
            ->latest('submitted_at')
            ->latest();
    }

    $exams = Exam::query()
        ->with($examRelations)
        ->when(auth()->check() && ! auth()->user()->is_admin, function ($query) {
            $query->where('academic_class_id', auth()->user()->academic_class_id);
        })
        ->latest()
        ->get();

    $givenGiftAwards = GiftAward::query()
        ->with(['attempt.user.academicClass', 'attempt.user.school', 'attempt.exam'])
        ->where('status', 'given')
        ->latest('given_at')
        ->take(12)
        ->get();

    return view('welcome', compact('exams', 'givenGiftAwards'));
})->name('home');

Route::get('/locale/{locale}', function (string $locale) {
    abort_unless(in_array($locale, ['bn', 'en'], true), 404);

    session(['locale' => $locale]);

    return redirect()->back(fallback: route('home'));
})->name('locale.switch');

Route::get('/exams', [ClassExamController::class, 'directory'])->name('exams.directory');

Route::get('/classes/{classSlug}/exams', [ClassExamController::class, 'index'])
    ->where('classSlug', '[A-Za-z0-9\-]+')
    ->name('classes.exams');

Route::get('/classes/{academicClass}/{slug}/exams', function (AcademicClass $academicClass) {
    return redirect()->route('classes.exams', $academicClass->slug, 301);
})->whereNumber('academicClass')->where('slug', '[A-Za-z0-9\-]+');

Route::get('/classes/{academicClass}/exams', function (AcademicClass $academicClass) {
    return redirect()->route('classes.exams', $academicClass->slug, 301);
})->whereNumber('academicClass');

Route::view('/privacy-policy', 'pages.privacy-policy')->name('privacy-policy');
Route::view('/about-us', 'pages.about-us')->name('about-us');
Route::view('/contact-us', 'pages.contact-us')->name('contact-us');

Route::get('/robots.txt', function () {
    return response(implode("\n", [
        'User-agent: *',
        'Disallow: /admin',
        'Disallow: /login',
        'Disallow: /register',
        'Disallow: /my-profile',
        'Disallow: /my-results',
        'Disallow: /exam-attempts',
        '',
        'Sitemap: ' . route('sitemap'),
    ]), 200)->header('Content-Type', 'text/plain');
})->name('robots');

Route::get('/sitemap.xml', function () {
    $urls = collect([
        ['loc' => route('home'), 'priority' => '1.0'],
        ['loc' => route('exams.directory'), 'priority' => '0.9'],
        ['loc' => route('about-us'), 'priority' => '0.7'],
        ['loc' => route('contact-us'), 'priority' => '0.7'],
        ['loc' => route('privacy-policy'), 'priority' => '0.5'],
    ])->merge(
        AcademicClass::query()
            ->orderBy('name')
            ->get()
            ->map(fn (AcademicClass $class) => [
                'loc' => route('classes.exams', $class->slug),
                'priority' => '0.6',
            ])
    )->merge(
        Exam::query()
            ->withCount('questions')
            ->whereHas('questions')
            ->where(fn ($query) => $query->whereNull('ends_at')->orWhereDate('ends_at', '>=', today()))
            ->latest()
            ->get()
            ->map(fn (Exam $exam) => [
                'loc' => route('exams.show', $exam->slug),
                'priority' => '0.8',
            ])
    );

    $items = $urls->map(fn ($url) => implode('', [
        '    <url>',
        '<loc>' . e($url['loc']) . '</loc>',
        '<changefreq>weekly</changefreq>',
        '<priority>' . e($url['priority']) . '</priority>',
        '</url>',
    ]))->implode("\n");

    $xml = implode("\n", [
        '<?xml version="1.0" encoding="UTF-8"?>',
        '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">',
        $items,
        '</urlset>',
    ]);

    return response($xml, 200)->header('Content-Type', 'application/xml');
})->name('sitemap');

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
    Route::get('/admin/login', [AuthController::class, 'showAdminLogin'])->name('admin.login');
    Route::post('/admin/login', [AuthController::class, 'login']);
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register']);
});

Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth')->name('logout');

Route::middleware('admin')->group(function () {
    Route::get('/admin', [AdminDashboardController::class, 'index'])->name('admin.index');
    Route::get('/admin/questions/create', [QuestionController::class, 'create'])->name('admin.questions.create');
    Route::get('/admin/exams', [ExamController::class, 'index'])->name('admin.exams.index');
    Route::get('/admin/exams/data', [ExamController::class, 'data'])->name('admin.exams.data');
    Route::get('/admin/exams/{exam}/results', [AdminResultController::class, 'exam'])->name('admin.exams.results');
    Route::get('/admin/results', [AdminResultController::class, 'index'])->name('admin.results.index');
    Route::get('/admin/custom-results', [AdminCustomResultController::class, 'index'])->name('admin.custom-results.index');
    Route::get('/admin/gift-recipients', [AdminGiftRecipientController::class, 'index'])->name('admin.gift-recipients.index');
    Route::patch('/admin/gift-recipients/{attempt}/given', [AdminGiftRecipientController::class, 'markGiven'])->name('admin.gift-recipients.given');
    Route::get('/admin/students', [AdminStudentController::class, 'index'])->name('admin.students.index');
    Route::get('/admin/students/data', [AdminStudentController::class, 'data'])->name('admin.students.data');
    Route::patch('/admin/students/{student}/password', [AdminStudentController::class, 'updatePassword'])->name('admin.students.password.update');
    Route::middleware('super_admin')->group(function () {
        Route::get('/admin/users', [AdminUserController::class, 'index'])->name('admin.users.index');
        Route::get('/admin/users/create', [AdminUserController::class, 'create'])->name('admin.users.create');
        Route::post('/admin/users', [AdminUserController::class, 'store'])->name('admin.users.store');
    });
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
    Route::post('/questions/bulk', [QuestionController::class, 'storeBulk'])->name('questions.bulk.store');
    Route::patch('/questions/{question}', [QuestionController::class, 'update'])->name('questions.update');
    Route::delete('/questions/{question}', [QuestionController::class, 'destroy'])->name('questions.destroy');
    Route::post('/exams', [ExamController::class, 'store'])->name('exams.store');
    Route::patch('/exams/{exam}', [ExamController::class, 'update'])->name('exams.update');
    Route::delete('/exams/{exam}', [ExamController::class, 'destroy'])->name('exams.destroy');
});

Route::middleware('auth')->group(function () {
    Route::get('/my-profile', [AuthController::class, 'profile'])->name('profile.show');
    Route::get('/my-profile/edit', [AuthController::class, 'editProfile'])->name('profile.edit');
    Route::patch('/my-profile', [AuthController::class, 'updateProfile'])->name('profile.update');
    Route::get('/custom-exams/create', [CustomExamController::class, 'create'])->name('custom-exams.create');
    Route::post('/custom-exams', [CustomExamController::class, 'store'])->name('custom-exams.store');
    Route::get('/custom-exams/chapters/options', [CustomExamController::class, 'chapterOptions'])->name('custom-exams.chapters.options');
    Route::get('/custom-exams/questions/count', [CustomExamController::class, 'questionCount'])->name('custom-exams.questions.count');
    Route::get('/custom-exams/results', [CustomExamController::class, 'results'])->name('custom-exams.results');
    Route::get('/custom-exams/{customExam}', [CustomExamController::class, 'show'])->name('custom-exams.show');
    Route::post('/custom-exams/{customExam}/submit', [CustomExamController::class, 'submit'])->name('custom-exams.submit');
    Route::get('/custom-exam-attempts/{attempt}', [CustomExamController::class, 'result'])->name('custom-exam-attempts.result');
    Route::get('/my-results', [ExamAttemptController::class, 'myResults'])->name('exam-attempts.index');
    Route::get('/exams/{exam}/results', [ExamAttemptController::class, 'allResults'])->name('exams.results');
    Route::post('/exams/{exam}/submit', [ExamAttemptController::class, 'submit'])->name('exams.submit');
    Route::get('/exam-attempts/{attempt}', [ExamAttemptController::class, 'result'])->name('exam-attempts.result');
});

Route::get('/exams/{exam}', function (Exam $exam) {
    return redirect()->route('exams.show', $exam->slug, 301);
})->whereNumber('exam');

Route::get('/exams/{examSlug}', [ExamAttemptController::class, 'show'])
    ->where('examSlug', '[A-Za-z0-9\-]+')
    ->name('exams.show');
