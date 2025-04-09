<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\TagController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\PostController;

use function App\apiResponse;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/
Route::post('/tokens/create', function (Request $request) {
    $token = $request->user()->createToken($request->token_name);
    return ['token' => $token->plainTextToken];
});
Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::group([
    'middleware' => 'api',
    'prefix' => 'auth',
    'controller' => AuthController::class
], function () {
    Route::post('/register', 'register');
    Route::post('/login', 'login');
    Route::get('/logout', 'logout');
});



Route::group([
    'middleware' => ['api','auth:sanctum'],
    'prefix' => 'posts',
    'controller' => PostController::class
], function () {
    Route::get('/', 'getAllPosts');
    Route::get('/user/{user_id}', 'viewUserPosts');
    Route::get('/{post_id}', 'viewUserPost');
    Route::post('/', 'createPost');
    Route::post('/{post_id}', 'updatePost');
    Route::delete('/{post_id}', 'deletePostSoftly');
    Route::get('/deleted/{user_id}', 'viewDeletedPosts');
    Route::get('/restore/{post_id}', 'restoreDeletedPost');
});
Route::get('postimage/{post_id}', [PostController::class, 'getPostImage'])->middleware('api');
Route::get('/stats', [PostController::class, 'stats'])->middleware('api');

Route::any('{url}', function () {
    return apiResponse(401, "", "this url not found check parmater");
})->where('url', '.*')->middleware('api');
