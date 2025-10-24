<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\CourseSectionController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\CourseController;
use App\Http\Controllers\Api\MentorController;
use App\Http\Controllers\Api\PricingController;
use App\Http\Controllers\Api\TransactionController;

//login
Route::post('/login', [AuthController::class, 'login']);

//course populer, detail course, all-course, course by category, CRUD mentor
Route::get('/courses/popular', [CourseController::class, 'getPopularCourses']);
Route::get('/courses/{slug}', [CourseController::class, 'show']);
Route::get('/courses', [CourseController::class, 'index']);
Route::get('/courses/category/{categorySlug}', [CourseController::class, 'courseByCategory']);

//CRUD Course
Route::middleware('auth:sanctum')->group(function () {

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
Route::get('/courses/{courseId}/sections', [CourseSectionController::class, 'listSections']);
Route::get('/sections/{sectionId}', [CourseSectionController::class, 'showSection']);
Route::get('/sections/{sectionId}/contents', [CourseSectionController::class, 'listContents']);
Route::get('/contents/{contentId}', [CourseSectionController::class, 'showContent']);

//pricing
Route::get('courses/{courseId}/pricings', [PricingController::class, 'listPricings']);

//transaction
Route::post('transactions', [TransactionController::class, 'store'])->middleware('auth:sanctum');








//category, popular category (-1)
Route::get('/categories', [CategoryController::class, 'index']);


//all-mentor, cari mentor berdasarkan nama, mentor by course, mentor by category (done)
Route::get('/mentors', [MentorController::class, 'index']);
Route::get('/mentors/{userId}', [MentorController::class, 'show']);
Route::get('/mentors/{mentorId}/courses', [MentorController::class, 'coursesTaught']);
Route::get('/mentors/category/{categorySlug}', [MentorController::class, 'mentorsByCategory']);
