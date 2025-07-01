<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
    <meta name="description" content="" />
    <meta name="author" content="" />
    <title>Log Viewer</title>
    <!-- CSS only -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.1/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-F3w7mX95PdgyTmZZMECAngseQB83DfGTowi0iMjiWaeVhAn4FJkqJByhZMI3AhiU" crossorigin="anonymous">
    <script src="https://use.fontawesome.com/releases/v6.3.0/js/all.js" crossorigin="anonymous"></script>
</head>
<style>
    .label-title {
        font-weight: bold;
        color: #495057;
    }

    .log-line {
        display: flex;
        font-family: monospace;
    }

    .log-lineno {
        width: 10px;
        min-width: 10px;
        flex-shrink: 0;                /* ✅ 不允許縮小 */
        background-color: #f0f0f0;
        color: #888;            text-align: right;
        margin-right: 10px;
        user-select: none; /* 👈 這樣就不能被選取 */
        pointer-events: none;
        opacity: 0.6;
    }

    .log-text {
        flex: 1;                       /* ✅ 讓內容區可以自動撐寬 */
        word-break: break-all;        /* ✅ 長字串可以斷行避免橫向捲軸 */
        border-bottom: 1px solid #ddd;
    }

    .log-text:hover {
        background-color: #b6d4fe; /* 鼠標懸停時的背景色 */
    }

    .log-page {
        border-bottom: 1px solid #ddd;
    }

    #logContent {
        font-family: Consolas, Menlo, monospace;
        font-size: 14px;
        overflow-y: auto;
        border: 1px solid #ddd;
        box-shadow: inset 0 1px 3px rgba(0,0,0,0.05);
        max-height: 600px;
        padding: 1rem;
        border-radius: 6px;
        background-color: #fdfdfd;
        color: #333;
    }

    .wrap-on {
        white-space: pre-wrap !important;
        word-break: break-word;
    }

    .wrap-off {
        white-space: pre !important;
        overflow-x: auto;
    }

    .loading::before {
        content: '載入中...';
        display: block;
        background: rgba(255, 255, 255, 0.75);
        text-align: center;
        padding: 0.5rem;
        position: sticky;
        top: 0;
        z-index: 10;
        font-weight: bold;
        color: #333;
    }
</style>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js" crossorigin="anonymous"></script>
<body class="sb-nav-fixed">
    <div class="container-fluid py-4">

        <div class="mb-3 px-2 border-start border-5 border-primary">
            <h4>Log File 檢視</h4>
        </div>
        <div class="card shadow">
            <div class="card-body">
                <form method="GET" class="mb-3">
                    <div class="row g-3">
                        <div class="col-md-2 mb-2">
                            <label for="logFileSelect" class="form-label">log 檔名關鍵字</label>
                            <div class="input-group">
                                <input type="text" id="logSearchInput" class="form-control  form-select-sm shadow-sm border-secondary" placeholder="🔍 搜尋 log 檔名關鍵字">
                                <button id="searchLogListBtn" class="btn btn-outline-secondary btn-sm" title="搜尋 Log 檔案">
                                    <i class="fa-solid fa-magnifying-glass-arrow-right"></i>
                                </button>
                            </div>
                        </div>
                        <div class="col-md-7">
                            <div class="d-flex justify-content-start  gap-2">
                                <label for="logFileSelect" class="form-label">選擇 log 檔案</label>
                            </div>
                            <div class="d-flex justify-content-between">
                                <div class="input-group">
                                    <button type="button" id="reloadLogListBtn" class="btn btn-outline-secondary btn-sm" title="列表重新載入">
                                        <i class="fa-solid fa-rotate"></i>
                                    </button>
                                    <button type="button" id="toggleAutoRefreshBtn" class="btn btn-sm btn-outline-secondary text-success">
                                        ⟳ 自動更新：ON
                                    </button>

                                    <select class="form-select form-select-sm shadow-sm border-secondary" id="logFileSelect"></select>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <label for="grepInput" class="form-label">搜尋 LOG 內容</label>
                            <input type="text" id="grepInput" class="form-control form-control-sm border-secondary" placeholder="輸入關鍵字">
                        </div>
                        <div class="col-md-1 d-flex align-items-end pb-2">
                            <button type="button" id="loadBtn" class="btn btn-primary btn-sm w-100">載入</button>
                        </div>
                    </div>
                </form>

                <div class="mb-2 d-flex justify-content-between">
                    <div id="fileMeta" class="text-muted small"></div>

                    <div>
                        <button id="loadMoreBtn" class="btn btn-outline-primary btn-sm" style="display:none;">
                            ↑ 載入上一段 (每次 500 行)
                        </button>
                        <button id="toggleWrapBtn" class="btn btn-secondary btn-sm d-none">換行：開啟中</button>
                    </div>
                </div>

                <div class="card">
                    <div class="card-body wrap-on m-0" id="logContent" style="font-size: 0.85rem; height: 65vh; overflow-y: auto;"></div>
                </div>

            </div>
        </div>
        <div id="loadingSpinner" class="d-none text-center mt-2">
            <div class="spinner-border text-secondary" role="status">
                <span class="visually-hidden">Loading...</span>
            </div>
        </div>

    </div>
    <script>
        // ✅ 外層定義函式
        function toggleWrap() {
            const logArea = document.getElementById('logContent');
            const btn = document.getElementById('toggleWrapBtn');
            const isWrapped = logArea.classList.contains('wrap-on');

            if (isWrapped) {
                logArea.classList.remove('wrap-on');
                logArea.classList.add('wrap-off');
                btn.textContent = '換行：關閉中';
            } else {
                logArea.classList.remove('wrap-off');
                logArea.classList.add('wrap-on');
                btn.textContent = '換行：開啟中';
            }
        }

        document.addEventListener("DOMContentLoaded", function () {
            const logSelect = document.getElementById('logFileSelect');
            const grepInput = document.getElementById('grepInput');
            const loadBtn = document.getElementById('loadBtn');
            const logContent = document.getElementById('logContent');
            const fileMeta = document.getElementById('fileMeta');
            const toggleWrapBtn = document.getElementById('toggleWrapBtn');
            const reloadLogListBtn = document.getElementById('reloadLogListBtn');
            const loadMoreBtn = document.getElementById('loadMoreBtn');
            const searchLogListBtnBtn = document.getElementById('searchLogListBtn');
            const searchInput = document.getElementById('logSearchInput');
            const toggleAutoRefreshBtn = document.getElementById('toggleAutoRefreshBtn');

            const API_URL = '{{ $api_url }}';

            let autoRefreshEnabled = true;
            let autoRefreshTimer = null;

            let logListCache = [];


            // 初始狀態
            logContent.classList.add('wrap-on');

            // 綁定按鈕事件
            toggleWrapBtn.addEventListener('click', toggleWrap);

            // 🧩 1. 載入 log 檔案清單
            async function loadLogList(search = '') {
                const res = await fetch(`${API_URL}?search=${encodeURIComponent(search)}`);
                const json = await res.json();
                if (!json.status) return;

                logListCache = json.data; // ✅ 儲存 log 清單（原始資料）


                logSelect.innerHTML = '<option disabled  value="" selected>載入 Log 列表完成，請選擇</option>';

                renderLogList(logListCache);
            }

            function renderLogList(data) {
                logSelect.innerHTML = '<option disabled selected>載入 Log 列表完成，請選擇</option>';
                data.forEach(file => {
                    const opt = document.createElement('option');
                    opt.value = file.name;
                    opt.textContent = `${file.name} (${file.size_kb} KB) -- ${file.updated_at}`;
                    logSelect.appendChild(opt);
                });
            }

            // 🧩 2. 取得 log 檔內容
            let currentPage = 1;

            async function loadLogChunk(logFile, keyword, page = 1, append = false) {

                console.log(`loadLogChunk() - file: ${logFile}, grep: ${keyword}, page: ${page}, append: ${append}`);

                const url = `${API_URL}/view?file=${encodeURIComponent(logFile)}&grep=${encodeURIComponent(keyword)}&page=${page}`;
                const res = await fetch(url);
                const json = await res.json();

                console.log('url:', url);
                console.log('json:', json);

                if (!json.status) throw new Error(json.message || '讀取失敗');

                currentPage = page;

                loadMoreBtn.style.display = json.has_more ? 'inline-block' : 'none';

                return json;
            }

            // 🧩 3. 渲染畫面內容
            function renderLogContent(data, keyword) {

                const lines = data.contents || [];
                if (lines.length === 0) {
                    logContent.innerHTML = '<div class="text-muted">(無內容)</div>';
                    return;
                }

                const logPageContent = lines.map((line, idx) => {
                    const highlightedLine = highlightKeyword(line, keyword);
                    return  addLineNumbers(highlightedLine, "-");
                }).join('');

                logContent.insertAdjacentHTML('afterbegin', `<div class="log-page">${logPageContent}</div>`);

                fileMeta.innerHTML = `大小：${data.size_kb} KB ` +
                    (data.is_large ? '（超過 5 MB 的大檔案）' : '') ;
                // fileMeta.innerHTML += (data.command_used ? `<code>${data.command_used}</code>` : '');

                toggleWrapBtn.classList.remove('d-none');

                // 顯示「載入更多」按鈕
                const loadMoreBtn = document.getElementById('loadMoreBtn');
                if (loadMoreBtn) {
                    loadMoreBtn.style.display = data.has_more ? 'inline-block' : 'none';
                    loadMoreBtn.dataset.page = data.page + 1; // 下次的頁碼
                }
            }

            function addLineNumbers(text, lineNo) {
                const lines = text.split(/\r?\n/);
                return lines.map((line, index) => {
                    return `<div class="log-line"><span class="log-lineno">${lineNo}</span><span class="log-text">${line}</span></div>`;
                }).join('');
            }

            function highlightKeyword(text, keyword) {
                const safeText = (text ?? '').toString(); // 保證為字串
                if (!keyword) return escapeHtml(safeText);
                const regex = new RegExp(`(${escapeReg(keyword)})`, 'gi');
                return escapeHtml(safeText).replace(regex, `<span class="bg-warning px-1 rounded">$1</span>`);
            }

            // ✨ HTML escape
            function escapeHtml(str) {
                return str.replace(/[&<"'>]/g, tag => ({
                    '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;'
                })[tag]);
            }

            // 🔐 RegExp escape
            function escapeReg(text) {
                const safeText = (text ?? '').toString();
                return safeText.replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
            }


            function setLoadingState(isLoading) {
                // 控制按鈕
                loadBtn.disabled = isLoading;
                reloadLogListBtn.disabled = isLoading;
                loadMoreBtn.disabled = isLoading;
                toggleWrapBtn.disabled = isLoading;

                const spinner = document.getElementById('loadingSpinner');

                // 可選：增加 loading 遮罩（自行定義 .loading 遮罩樣式）
                if (isLoading) {
                    logContent.classList.add('loading');
                } else {
                    logContent.classList.remove('loading');
                }

                if (spinner) spinner.classList.toggle('d-none', !isLoading);

                // 可選：讓游標變成等待
                document.body.style.cursor = isLoading ? 'wait' : 'default';
            }

            function startAutoRefresh() {
                const autoloadTime = {{ $auto_refresh_time }};
                autoRefreshTimer = setInterval(async () => {
                    console.log('⟳ 自動更新 log 檔案列表...');
                    await loadLogList();
                }, autoloadTime * 1000); // 每 5 分鐘
            }

            function stopAutoRefresh() {
                if (autoRefreshTimer) {
                    clearInterval(autoRefreshTimer);
                    autoRefreshTimer = null;
                }
            }

            toggleAutoRefreshBtn.addEventListener('click', () => {
                autoRefreshEnabled = !autoRefreshEnabled;

                if (autoRefreshEnabled) {
                    toggleAutoRefreshBtn.textContent = '⟳ 自動更新：ON';
                    toggleAutoRefreshBtn.classList.remove('text-danger');
                    toggleAutoRefreshBtn.classList.add('text-success');
                    console.log('啟用自動更新');
                    startAutoRefresh();
                } else {
                    toggleAutoRefreshBtn.textContent = '⟳ 自動更新：OFF';
                    toggleAutoRefreshBtn.classList.remove('text-success');
                    toggleAutoRefreshBtn.classList.add('text-danger');
                    console.log('關閉自動更新');
                    stopAutoRefresh();
                }
            });

            // 🚀 初始化主流程
            async function init() {
                console.log('初始化 Log Reader...');

                console.log("Search Input:", searchInput);

                // ✅ 綁定搜尋欄位 input 事件
                if (searchInput) {

                    searchInput.addEventListener('input', (e) => {
                        const keyword = e.target.value.trim().toLowerCase();
                        console.log(`搜尋 log 檔名關鍵字：${keyword}`);

                        const filtered = logListCache.filter(item =>
                            item.name.toLowerCase().includes(keyword)
                        );
                        renderLogList(filtered);
                    });
                }

                // ✅ 頁面載入初次載入 log 清單（不帶關鍵字）
                await loadLogList();

                startAutoRefresh(); // 預設啟用自動更新

                loadBtn.addEventListener('click', async () => {
                    const logFile = logSelect.value;
                    const keyword = grepInput.value.trim();
                    if (!logFile) {
                        alert('請選擇 log 檔案');
                        return;
                    }

                    logContent.textContent = '';
                    fileMeta.textContent = '';
                    toggleWrapBtn.classList.add('d-none');

                    try {
                        setLoadingState(true);
                        const data = await loadLogChunk(logFile, keyword);
                        renderLogContent(data, keyword);
                    } catch (err) {
                        logContent.textContent = err.message;
                    } finally {
                        setLoadingState(false);
                    }
                });

                reloadLogListBtn.addEventListener('click', async () => {
                    try {
                        setLoadingState(true);
                        await loadLogList();
                    } finally {
                        setLoadingState(false);
                    }
                });

                // 點擊「載入更多」
                loadMoreBtn.addEventListener('click', async () => {
                    const logFile = logSelect.value;
                    const keyword = grepInput.value.trim();

                    try {
                        setLoadingState(true);
                        const data = await loadLogChunk(logFile, keyword, currentPage + 1, true);
                        renderLogContent(data, keyword);
                    } finally {
                        setLoadingState(false);
                    }
                });

            }

            init().catch(err => {
                console.error('初始化錯誤:', err);
            }); // 👈 啟動流程
        });
    </script>
</body>
</html>