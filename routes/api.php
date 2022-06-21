<?php

use App\Http\Controllers\AnswerController;
use App\Http\Controllers\ExportController;
use App\Http\Controllers\HelperActivityController;
use App\Http\Controllers\HelperResponseUserRatingController;
use App\Http\Controllers\LanguageController;
use App\Http\Controllers\PendingQuestionController;
use App\Http\Controllers\QuestionController;
use App\Http\Controllers\TopicController;
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

Route::post('/question', [PendingQuestionController::class, 'store']);

Route::get('/languages', [LanguageController::class, 'api']);

Route::get('/topics', [TopicController::class, 'api']);

Route::get('/answers/{language:code}', [AnswerController::class, 'apiForLanguage']);

Route::get('/questions/{language:code}', [QuestionController::class, 'apiForLanguage']);
Route::get('/questions/{language:code}/{topic}', [QuestionController::class, 'apiForLanguageAndTopic']);

Route::get('/export/{language:code}', [ExportController::class, 'export']);

Route::post('/helper_activity', [HelperActivityController::class, 'store']);

Route::post('/helper_response_user_ratings', [HelperResponseUserRatingController::class, 'store']);
