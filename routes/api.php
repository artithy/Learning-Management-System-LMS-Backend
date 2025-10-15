<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\CourseCategoryController;
use App\Http\Controllers\CourseController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\StudentController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');
Route::post('/register', [AdminController::class, 'register']);
Route::post('/login', [AdminController::class, 'login']);
Route::post('/student/register', [StudentController::class, 'register']);
Route::post('/student/login', [StudentController::class, 'login']);
Route::middleware(['auth.jwt'])->group(function () {
    Route::get('/categories', [CourseCategoryController::class, 'getAllCategory']);
    Route::get('/categories/{id}', [CourseCategoryController::class, 'getCategoryById']);
    Route::post('/categories', [CourseCategoryController::class, 'createCategory']);
    Route::post('/updateCategories/{id}', [CourseCategoryController::class, 'updateCategory']);
    Route::post('/deleteCategories/{id}', [CourseCategoryController::class, 'deleteCategory']);


    Route::post('/courses', [CourseController::class, 'createCourse']);
    Route::get('/courses', [CourseController::class, 'getAllCourses']);
    Route::get('/courses/{id}', [CourseController::class, 'getCourseById']);
    Route::post('/updateCourses/{id}', [CourseController::class, 'updateCourse']);
    Route::post('/deleteCourses/{id}', [CourseController::class, 'deleteCourse']);
});
