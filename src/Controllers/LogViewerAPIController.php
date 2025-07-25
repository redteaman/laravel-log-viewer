<?php

namespace Redteaman\LogViewer\Controllers;

use Illuminate\Routing\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class LogViewerAPIController extends Controller
{
    protected string $logBasePath = '';

    public function __construct()
    {
        $this->logBasePath = storage_path('logs');
    }

    // 取得所有 .log 檔案列表
    public function list(Request $request): \Illuminate\Http\JsonResponse
    {
        $keyword = trim($request->get('search'));

        $files = collect(File::allFiles($this->logBasePath))
            ->filter(fn($file) => Str::endsWith($file->getFilename(), '.log'))
            ->map(function ($file) {
                $fullPath = $file->getPathname();
                $relative = Str::after($fullPath, $this->logBasePath . DIRECTORY_SEPARATOR);
                $mtime = $file->getMTime();

                return [
                    'name' => $relative,
                    'folder' => dirname($relative),
                    'size_kb' => round($file->getSize() / 1024, 2),
                    'updated_at' => date('Y-m-d H:i:s', $mtime),
                    'timestamp' => $mtime,
                ];
            })
            ->when($keyword, function ($collection, $keyword) {
                return $collection->filter(function ($item) use ($keyword) {
                    return Str::contains($item['name'], $keyword);
                });
            })
            ->sortByDesc('timestamp')
            ->values();

        return response()->json(['status' => true, 'data' => $files]);
    }

    // 顯示指定 log 檔內容
    public function view(Request $request): \Illuminate\Http\JsonResponse
    {
        $relative = $request->input('file');
        $grep = trim($request->input('grep', ''));
        $perPage = (int) $request->input('lines', 500);
        $page = (int) $request->input('page', 1); // 1 = 最新尾段

        $filePath = $this->logBasePath . DIRECTORY_SEPARATOR . $relative;

        if (!File::exists($filePath) || !Str::endsWith($filePath, '.log')) {
            return response()->json([
                'status' => false,
                'message' => '檔案不存在或非法路徑'
            ], 400);
        }

        $fileSize = File::size($filePath);
        $escapedFile = escapeshellarg($filePath);
        $escapedGrep = escapeshellarg($grep);
        $safePattern = '/^[\w.\-@:+# ]*$/';

        // 🧠 計算要取幾行資料（倒數）
        $tailLines = $page * $perPage;


        // 🧠 建構 shell 指令
        $cmd = "tail -n {$tailLines} {$escapedFile}";
        if ($grep && preg_match($safePattern, $grep)) {
            $cmd .= " | grep {$escapedGrep}";
        }
        $cmd .= " | head -n {$perPage}";

        // 🧠 執行命令
        $output = shell_exec($cmd);
        $lines = $output ? explode("\n", trim($output)) : [];

        return response()->json([
            'status' => true,
            'size_kb' => round($fileSize / 1024, 2),
            'is_large' => $fileSize > (5 * 1024 * 1024),
            'grep' => $grep,
            'contents' => $lines,
            'command_used' => $cmd,
            'page' => $page,
            'has_more' => count($lines) >= $perPage, // 如果等於一整頁，可能還有更多
        ]);

    }
}
