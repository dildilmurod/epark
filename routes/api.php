<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});


Route::post('/register', 'UserAuthController@register');
Route::post('/login', 'UserAuthController@login');
Route::post('/login-social', 'UserAuthController@login_social');
Route::post('/update-password', 'UserAuthController@update_password');
Route::get('/verify', 'UserAuthController@verify');
Route::get('/activate-password', 'UserAuthController@activate_password');
Route::post('/resend-verification', 'UserAuthController@resend_verification');
Route::post('/reset', 'UserAuthController@reset');


Route::resource('courses', 'CourseAPIController');
Route::post('courses/{courses}', 'CourseAPIController@update');
Route::post('course-by-title', 'CourseAPIController@course_by_title');
Route::post('course-by-url', 'CourseAPIController@course_by_url');
Route::post('course-notification/{courses}', 'CourseAPIController@course_notification');
Route::get('course-enroll/{course}', 'CourseAPIController@course_enroll');
Route::get('my-courses', 'CourseAPIController@my_courses');

Route::post('attach-course-teacher/{courses}', 'CourseAPIController@attach_teacher');
Route::post('detach-course-teacher/{courses}', 'CourseAPIController@detach_teacher');

Route::post('attach-course-partner/{courses}', 'CourseAPIController@attach_partner');
Route::post('detach-course-partner/{courses}', 'CourseAPIController@detach_partner');




Route::resource('lessons', 'LessonAPIController');
Route::post('lessons/{lessons}', 'LessonAPIController@update');
Route::get('lesson-finished/{lesson}', 'LessonAPIController@lesson_finished');

Route::get('/user', 'UserController@users');
Route::get('/user/{id}', 'UserController@show');
Route::get('/users-list', 'UserController@users_list');
Route::delete('/user/{id}', 'UserController@destroy');
Route::post('/change-role/{id}', 'UserController@change_role');
Route::get('/current-user', 'UserController@current_user');
Route::get('/back-user', 'UserController@back_user');
Route::post('/search-by-email', 'UserController@search_by_email');
Route::post('/user-update', 'UserController@update');


Route::resource('course-features', 'CourseFeatureAPIController');
Route::post('course-features/{course_features}', 'CourseFeatureAPIController@update');

Route::resource('teachers', 'TeacherAPIController');
Route::post('teachers/{teachers}', 'TeacherAPIController@update');

Route::resource('partners', 'PartnerAPIController');
Route::post('partners/{partners}', 'PartnerAPIController@update');

Route::resource('faqs', 'FaqAPIController');
Route::post('faqs/{faqs}', 'FaqAPIController@update');

Route::resource('reviews', 'ReviewAPIController');
Route::post('reviews/{reviews}', 'ReviewAPIController@update');




