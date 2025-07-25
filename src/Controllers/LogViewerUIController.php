<?php

namespace Redteaman\LogViewer\Controllers;

use Illuminate\Routing\Controller;

class LogViewerUIController extends Controller
{
    public function index() {

        return view("log_viewer::log_viewer", [
            'api_url' => '/api/log-viewer',
            'auto_refresh_time' => 300, // 5 minutes
        ]);
    }
}
