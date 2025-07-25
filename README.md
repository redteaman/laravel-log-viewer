# Laravel Log Viewer

✅ 方法 1：本地 path 套件方式（適合內部開發或測試）
如果你不想每次都 git clone，可以直接把套件放在 Laravel 專案中的 packages/ 資料夾。

🔧 步驟
1. 將套件放入新專案的 `packages/` 目錄：
   ```
   your-laravel-project/
        └── packages/
            └── redteaman/
                └── laravel-log-viewer/  ← 複製這整包進來
   ```
2. 修改專案 composer.json
   ```
   "repositories": [
     {
         "type": "path",
         "url": "packages/redteaman/laravel-log-viewer",
         "options": {
             "symlink": false
         }
       }
   ],
   "require": {
         "redteaman/laravel-log-viewer": "dev-master"
   },
   "minimum-stability": "dev",
   "prefer-stable": true
   ```
3. 安裝套件
   ```bash
   composer update
   composer dump-autoload
   php artisan optimize:clear
   ```
4. 驗證 route 載入
   ```bash
   php artisan route:list | grep log-viewer
   ```

⚠️ 錯誤訊息：

```bash
Symlink from "/var/www/mylocal/packages/redteaman/laravel-log-viewer" to "/var/www/mylocal/vendor/redteaman/laravel-log-viewer" failed!
```

表示當你執行 composer update 時，Composer 嘗試**用 symlink（符號連結）**的方式，把你 packages/redteaman/laravel-log-viewer 的資料夾連結到 vendor/ 下，但失敗了。

**解決方法**

打開你主專案的 composer.json：

```json
"repositories": [
  {
    "type": "path",
    "url": "packages/redteaman/laravel-log-viewer",
    "options": {
      "symlink": false
    }
  }
]
```

📌 這會讓 composer 改用「複製」的方式，而不是「符號連結」，避免 symlink 權限/檔案系統限制問題。
然後再執行 `composer update`，安裝完成後，可以把 symlink 改回 true。


✅ 方法 2：GitHub 套件安裝（建議用於共用或正式專案）
   若你已將套件放上 GitHub，可直接從 GitHub 安裝。

🔧 步驟
1. 在你的 Laravel 專案 composer.json 中加上：
   ```json
   "repositories": [
      {
        "type": "vcs",
        "url": "https://github.com/redteaman/laravel-log-viewer"
      }
   ],
   "require": {
      "redteaman/laravel-log-viewer": "dev-main"
   },
   "minimum-stability": "dev",
   "prefer-stable": true
   ```
   ⚠️ 需將你的 GitHub 套件設定為公開，或是設定憑證來讀取私有 repo。

2. 執行：
   ```bash
   composer require redteaman/laravel-log-viewer:dev-main
   ```
   
✅ Laravel 12 注意事項
不需額外註冊 provider：
你的套件 composer.json 有設定：
```json
"extra": {
    "laravel": {
        "providers": [
            "Redteaman\\\\LogViewer\\\\LogViewerServiceProvider"
        ]
    }
}
```
Laravel 會自動載入，不需手動加入 config/app.php。

✅ 額外提示

| 項目      | 說明                                              |
| ------- | ----------------------------------------------- |
| 如何升級套件  | 只要在套件目錄內 pull 最新程式，再到主專案執行 `composer update` 即可 |
| 多專案共用套件 | 建議推上 GitHub 或 GitLab，用 `vcs` 安裝                 |
| 套件正式釋出  | 建議打 `v1.0.0` tag，這樣可以避免 dev-master 的穩定性問題       |

