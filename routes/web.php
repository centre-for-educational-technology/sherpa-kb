<?php

use App\Http\Controllers\AnswerController;
use App\Http\Controllers\CSRFTokenController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\PendingQuestionController;
use App\Http\Controllers\QuestionController;
use App\Http\Controllers\StatisticsController;
use App\Http\Controllers\TopicController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Auth;
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
    if (Auth::check()) {
        return redirect('home');
    }

    return view('welcome');
});

Auth::routes([
    'register' => false,
    'reset' => false,
    'verify' => false,
]);

Route::get('/home', [HomeController::class, 'index'])->name('home');

Route::get('/questions', [QuestionController::class, 'list']);
Route::get('/questions/states', [QuestionController::class, 'states']);
Route::post('/questions', [QuestionController::class, 'store']);
Route::put('/questions/{question}', [QuestionController::class, 'update']);
Route::delete('/questions/{question}', [QuestionController::class, 'delete']);

Route::get('/pending_questions', [PendingQuestionController::class, 'list']);
Route::get('/pending_questions/states', [PendingQuestionController::class, 'states']);
Route::put('/pending_questions/{pending_question}', [PendingQuestionController::class, 'update']);
Route::delete('/pending_questions/{pending_question}', [PendingQuestionController::class, 'delete']);

Route::get('/answers', [AnswerController::class, 'list']);
Route::get('/answers/states', [AnswerController::class, 'states']);
Route::post('/answers', [AnswerController::class, 'store']);
Route::put('/answers/{answer}', [AnswerController::class, 'update']);
Route::delete('/answers/{answer}', [AnswerController::class, 'delete']);

Route::get('/users', [UserController::class, 'index'])->name('users');
Route::get('/users/roles', [UserController::class, 'roles']);
Route::post('/users', [UserController::class, 'store']);
Route::put('/users/{user}', [UserController::class, 'update']);
Route::delete('/users/{user}', [UserController::class, 'delete']);

Route::get('statistics', [StatisticsController::class, 'index']);

Route::prefix('topics')->group(function () {
    Route::get('/', [TopicController::class, 'list']);
    Route::post('/', [TopicController::class, 'store']);
    Route::put('{topic}', [TopicController::class, 'update']);
    Route::delete('{topic}', [TopicController::class, 'delete']);
});

Route::post('refresh_csrf_token', [CSRFTokenController::class, 'refresh']);
