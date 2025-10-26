<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\CourseCategoryController;
use App\Http\Controllers\CourseController;
use App\Http\Controllers\EnrollmentController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\StudentController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');
Route::post('/admin/register', [AdminController::class, 'register']);
Route::post('/admin/login', [AdminController::class, 'login']);
Route::post('/student/register', [StudentController::class, 'register']);
Route::post('/student/login', [StudentController::class, 'login']);
Route::get('/courses/public', [CourseController::class, 'getAllCoursesPublic']);
Route::get('/courses/{id}', [CourseController::class, 'getCourseById']);
Route::get('/courses/public/category/{id}', [CourseController::class, 'getCoursesByCategoryPublic']);
Route::get('/categories/public', [CourseCategoryController::class, 'getAllCategoryPublic']);



Route::middleware(['auth.jwt'])->group(function () {
    Route::get('/categories', [CourseCategoryController::class, 'getAllCategory']);
    Route::get('/categories/{id}', [CourseCategoryController::class, 'getCategoryById']);
    Route::post('/categories', [CourseCategoryController::class, 'createCategory']);
    Route::post('/updateCategories/{id}', [CourseCategoryController::class, 'updateCategory']);
    Route::post('/deleteCategories/{id}', [CourseCategoryController::class, 'deleteCategory']);

    Route::post('/courses', [CourseController::class, 'createCourse']);
    Route::get('/courses', [CourseController::class, 'getAllCourses']);
    Route::post('/updateCourses/{id}', [CourseController::class, 'updateCourse']);
    Route::post('/deleteCourses/{id}', [CourseController::class, 'deleteCourse']);

    Route::get('/enrollments', [EnrollmentController::class, 'allEnrollments']);
});

Route::middleware(['auth.jwt', 'role:student'])->group(function () {
    Route::get('/student/courses', [CourseController::class, 'getAllCourses']);
    Route::get('/student/courses/{id}', [CourseController::class, 'getCourseById']);

    Route::post('/enroll', [EnrollmentController::class, 'enroll']);
    Route::get('/student/enrollments', [EnrollmentController::class, 'studentEnrollments']);
});
