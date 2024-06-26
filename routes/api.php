<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\API\RegisterController;
use App\Http\Controllers\API\ProductController;
use App\Http\Controllers\API\FileController;
use App\Http\Controllers\API\FileShareController;
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

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::controller(RegisterController::class)->group(function(){
    Route::post('register', 'register');
    Route::post('login', 'login')->name('login');
});

Route::get('all-files', [FileController::class, 'allFiles']);
Route::get('user-files/{id}', [FileController::class, 'findFiles']);


Route::get('test', [FileController::class, 'test']);

Route::get('/file/d/{id}', [FileController::class, 'downloadFile'])->name('files.download');
Route::post('/file/unblock' , [FileController::class, 'unblockRequest'])->name('file.unblock');
Route::post('/file/download-date', [FileController::class, 'downloadAndDate']);
Route::get('admin/file/requests-list', [FileController::class, 'requestList']);
Route::get('/file/change-status/{id}', [FileController::class, 'changeStatus']);
Route::get('/logout', [RegisterController::class, 'logout']);

Route::post('files', [FileController::class, 'store']);
Route::get('file/delete/{id}', [FileController::class, 'destroy']);
Route::get('files/auto-delete', [FileController::class, 'autoDelete']);

Route::middleware('auth:sanctum')->group( function () {
    Route::resource('file-share', FileShareController::class);
    Route::get('link-generate/{id}', [FileShareController::class,'linkGenerate']);

});

