<?php

namespace App\Http\Controllers\UI;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class LogViewerController extends Controller
{
    public function index() {

        return view("log_viewer", [
            'api_url' => '/api/log_viewer',
            'auto_refresh_time' => 300, // 5 minutes
        ]);
    }
}
