<style>
    .modalSidebar {
        display: none;
        position: fixed;
        z-index: 1000;
        left: 0;
        top: 0;
        width: 100%;
        height: 100%;
        background-color: rgba(0, 0, 0, 0.9);
        justify-content: center;
        align-items: center;
    }

    .modal-content-Sidebar {
        max-width: 90%;
        max-height: 90%;
        margin: auto;
    }

    .close {
        position: absolute;
        top: 20px;
        right: 35px;
        color: #fff;
        font-size: 40px;
        font-weight: bold;
        cursor: pointer;
    }
</style>

<div id="sidebar" class="sidebar" style="background-color: #fae6b1;">
    <ul>
        <?php
        // 現在のページのファイル名を取得
        $currentPage = basename($_SERVER['PHP_SELF']);
        ?>
        <!-- ホームにいる場合はボタンを非表示 -->
        <?php if ($currentPage !== "index.php"): ?>
            <li class="btn"><a href="/DeepImpact/resources/views/index.php" class="common-btn">ホームに戻る</a></li>
        <?php endif; ?>
        <li class="btn">
            <button id="sidebar-click-btn" class="btn">ヘルプ</button>
            <div id="sidebar-popup-wrapper" style="display: none;">
                <div id="sidebar-popup-inside">
                    <div id="sidebar-close">X</div>
                    <div id="popup-content-sidebar">
                        <!-- ここにチュートリアルコンテンツが読み込まれます -->
                    </div>
                </div>
            </div>
        </li>
        <?php if ($currentPage !== "profile.php"): ?>
            <li class="btn"><a href="/DeepImpact/resources/views/login/profile.php" class="common-btn">プロフィール</a></li>
        <?php endif; ?>
        <?php if ($currentPage !== "friend.php"): ?>
            <li class="btn"><a href="/DeepImpact/resources/views/friend.php" class="common-btn">フレンド追加</a></li>
        <?php endif; ?>
        <?php if ($currentPage !== "cardlist.php"): ?>
            <li class="btn"><a href="/DeepImpact/resources/views/cardlist.php" class="common-btn">カード一覧</a></li>
        <?php endif; ?>
        <?php if ($currentPage !== "card_edit.php"): ?>
            <li class="btn"><a href="/DeepImpact/resources/views/card_edit.php" class="common-btn">デッキ編集</a></li>
        <?php endif; ?>
        <?php if ($currentPage !== "inbox.php"): ?>
            <li class="btn"><a href="/DeepImpact/resources/views/inbox.php" class="common-btn">メッセージ</a></li>
        <?php endif; ?>
    </ul>
    <!-- フッターセクション -->
    <div>
        <a href="/DeepImpact/resources/views/logout.php" class="logout-btn">ログアウト</a>
    </div>
</div>

<script>
    const sidebarClickBtn = document.getElementById('sidebar-click-btn');
    const sidebarPopupWrapper = document.getElementById('sidebar-popup-wrapper');
    const sidebarClose = document.getElementById('sidebar-close');
    const popupContentSidebar = document.getElementById('popup-content-sidebar');

    function loadTutorialSidebar() {
        const xhr = new XMLHttpRequest();
        xhr.open('GET', '/DeepImpact/resources/views/tutorial.php', true);
        xhr.onreadystatechange = function() {
            if (xhr.readyState === 4 && xhr.status === 200) {
                popupContentSidebar.innerHTML = xhr.responseText;

                // tutorial.php内の画像クリック処理を再定義
                const clickableImageSidebar = document.querySelectorAll('.clickableImage');

                clickableImageSidebar.forEach(image => {
                    image.addEventListener('click', function() {
                        const modalSidebar = document.getElementById('imageModal');
                        const modalImageSidebar = document.getElementById('modalImage');
                        modalSidebar.style.display = 'flex'; // モーダルを表示
                        modalImageSidebar.src = this.src; // クリックした画像のsrcをモーダルに設定
                    });
                });

                // モーダルを閉じる処理
                const closeModalSidebar = document.getElementById('closeModalSidebar');
                const modalSidebar = document.getElementById('imageModal');
                closeModalSidebar.addEventListener('click', function() {
                    modalSidebar.style.display = 'none'; // バツマークをクリックしてモーダルを閉じる
                });

                // モーダルの外側をクリックして閉じる
                modalSidebar.addEventListener('click', function(e) {
                    if (e.target === modalSidebar) {
                        modalSidebar.style.display = 'none'; // 外側をクリックしてモーダルを閉じる
                    }
                });
            } else {
                console.error("Error loading tutorial: " + xhr.status + " " + xhr.statusText);
            }
        };
        xhr.onerror = function() {
            console.error("Request failed.");
        };
        xhr.send();
    }

    // ルールボタンをクリックしたときにポップアップを表示し、チュートリアルを読み込む
    sidebarClickBtn.addEventListener('click', () => {
        sidebarPopupWrapper.style.display = "block";
        loadTutorialSidebar(); // コンテンツを動的に読み込む
    });

    // ポップアップの外側又は「x」のマークをクリックしたときポップアップを閉じる
    sidebarPopupWrapper.addEventListener('click', e => {
        if (e.target.id === sidebarPopupWrapper.id || e.target.id === sidebarClose.id) {
            sidebarPopupWrapper.style.display = 'none';
        }
    });
</script>