<?php

use Illuminate\Support\Facades\Route;
use Redteaman\LogViewer\Controllers\LogViewerUIController;

Route::get('/log-viewer', [LogViewerUIController::class, 'index']);
