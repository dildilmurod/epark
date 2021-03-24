<?php

use App\User;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

Route::get('/notify', function () {
//    User::find(737)->notify(new \App\Notifications\NewAction);    //single user
    $users = User::find(737);
    Notification::send($users, new \App\Notifications\NewAction("Test message"));   //multiple users
    return view('welcome');
});

Route::get('/email', function () {
    \Illuminate\Support\Facades\Mail::to('allfocuses@gmail.com')->send(new \App\Mail\WelcomeMail("Welcome message", "https://www.google.com/"));
    return 'success';
});

// Route::get('/user', function () {
//     return view('user.index');
// });

Route::get('/user', 'UserAuthController@users');
