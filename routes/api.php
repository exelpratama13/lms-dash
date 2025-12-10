<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\CertificateController;
use App\Http\Controllers\Api\CourseSectionController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\CourseController;
use App\Http\Controllers\Api\CourseProgressController;
use App\Http\Controllers\Api\MentorController;
use App\Http\Controllers\Api\PricingController;
use App\Http\Controllers\Api\TransactionController;
use App\Http\Controllers\Api\StatsController;
use App\Http\Controllers\Api\QuizAttemptController;
use App\Http\Controllers\Api\SocialiteController; // <-- Tambahkan baris ini

// Auth
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// Google Socialite
Route::get('/auth/google/redirect', [SocialiteController::class, 'redirectToGoogle']);
Route::get('/auth/google/callback', [SocialiteController::class, 'handleGoogleCallback']);

Route::post('/refresh', [AuthController::class, 'refresh']);
Route::group(['middleware' => 'auth:api'], function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/me', [AuthController::class, 'me']);
    Route::post('/me', [AuthController::class, 'updateProfile']);

    // My Courses
    Route::get('/my-courses', [CourseController::class, 'myCourses']);

    // Mark content as complete
    Route::post('/courses/{courseId}/contents/{contentId}/complete', [CourseProgressController::class, 'markAsComplete']);
    Route::delete('/courses/{courseId}/contents/{contentId}/complete', [CourseProgressController::class, 'markAsIncomplete']);

    // My Transactions
    Route::get('/my-transactions', [TransactionController::class, 'myTransactions']);

    // My Certificates
    Route::get('/my-certificates', [CertificateController::class, 'myCertificates']);
    Route::post('/certificates', [CertificateController::class, 'store'])->middleware('auth:api');
    // Regenerate certificate PDF for an existing certificate (owner only)
    Route::post('/certificates/{certificate}/regenerate', [CertificateController::class, 'regenerate'])->middleware('auth:api');

    // Show single transaction
    Route::get('/transactions/{bookingTrxId}', [TransactionController::class, 'show']);
});

//course populer, detail course, all-course, course by category
Route::get('/courses/search', [CourseController::class, 'search']);
Route::get('/courses/popular', [CourseController::class, 'getPopularCourses']);
Route::get('/courses/{slug}', [CourseController::class, 'show']);
Route::get('/courses', [CourseController::class, 'index']);
Route::get('/courses/category/{categorySlug}', [CourseController::class, 'courseByCategory']);

// Routes for accessing course materials, protected by auth and access check
Route::middleware(['auth:api', \App\Http\Middleware\CheckCourseAccess::class])->group(function () {
    Route::get('/materi/{slug}', [CourseController::class, 'materi']);
    Route::get('/courses/{courseId}/sections', [CourseSectionController::class, 'listSections']);
});

//CRUD Course
Route::middleware('auth:api')->group(function () {

    // CRUD Course (Wajib Autentikasi dan Otorisasi Role di Form Request)
    Route::post('/courses', [CourseController::class, 'store']);
    Route::put('/courses/{id}', [CourseController::class, 'update']);
    Route::delete('/courses/{id}', [CourseController::class, 'destroy']);

    // CRUD COURSE SECTION
    Route::post('/sections', [CourseSectionController::class, 'storeSection']);
    Route::put('/sections/{sectionId}', [CourseSectionController::class, 'updateSection']);
    Route::delete('/sections/{sectionId}', [CourseSectionController::class, 'destroySection']);

    // CRUD SECTION CONTENT
    Route::post('/contents', [CourseSectionController::class, 'storeContent']);
    Route::put('/contents/{contentId}', [CourseSectionController::class, 'updateContent']);
    Route::delete('/contents/{contentId}', [CourseSectionController::class, 'destroyContent']);
});

// course section & content
Route::get('/sections/{sectionId}', [CourseSectionController::class, 'showSection']);
Route::get('/sections/{sectionId}/contents', [CourseSectionController::class, 'listContents']);
Route::get('/contents/{contentId}', [CourseSectionController::class, 'showContent']);

//pricing
Route::get('courses/{courseId}/pricings', [PricingController::class, 'listPricings']);

//transaction
Route::post('transactions', [TransactionController::class, 'store'])->middleware('auth:api'); // Can be used for Midtrans by setting "payment_type": "midtrans"
Route::post('transactions/new-midtrans-payment', [TransactionController::class, 'storeNewMidtransTransaction'])->middleware('auth:api'); // New endpoint for Midtrans payment initiation

// New separate endpoint for Midtrans payment initiation
Route::post('new-transactions/midtrans-payment', [\App\Http\Controllers\Api\NewTransactionController::class, 'store'])->middleware('auth:api');

Route::post('payments/initiate', [\App\Http\Controllers\Api\PaymentController::class, 'store'])->middleware('auth:api'); // Dedicated endpoint for payment initiation


//category, popular category (-1)
Route::get('/categories', [CategoryController::class, 'index']);


//all-mentor, cari mentor berdasarkan nama, mentor by course, mentor by category (done)
Route::get('/mentors', [MentorController::class, 'index']);
Route::get('/mentors/{userId}', [MentorController::class, 'show']);
Route::get('/mentors/{mentorId}/courses', [MentorController::class, 'coursesTaught']);
Route::get('/mentors/category/{categorySlug}', [MentorController::class, 'mentorsByCategory']);


//stats
Route::get('/counts', [StatsController::class, 'getCounts']);

//midtrans
Route::post('/midtrans/webhook', [\App\Http\Controllers\Api\MidtransWebhookController::class, 'handle']);

//quizz
Route::middleware('auth:api')->group(function () {
    Route::get('/quiz-attempts', [QuizAttemptController::class, 'index']);
    Route::post('/quiz-attempts', [QuizAttemptController::class, 'store']);
});
