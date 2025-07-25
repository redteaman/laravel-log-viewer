<?php

use Illuminate\Support\Facades\Route;
use Redteaman\LogViewer\Controllers\LogViewerAPIController;

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

Route::prefix('log-viewer')->group(function () {
    Route::get('/list', [LogViewerAPIController::class, 'list']);
    Route::get('/view', [LogViewerAPIController::class, 'view']);
});
