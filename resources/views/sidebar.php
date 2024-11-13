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
        <li class="btn"><a href="/DeepImpact/resources/views/index.php" class="common-btn">ホームに戻る</a></li>
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
        <li class="btn"><a href="/DeepImpact/resources/views/login/profile.php" class="common-btn">プロフィール</a></li>
        <li class="btn"><a href="/DeepImpact/resources/views/friend.php" class="common-btn">フレンド</a></li>
        <li class="btn"><a href="/DeepImpact/resources/views/logout.php" class="common-btn">ログアウト</a></li>
        <li class="btn"><a href="/DeepImpact/resources/views/cardlist.php" class="common-btn">カード一覧</a></li>
        <li class="btn"><a href="/DeepImpact/resources/views/card_edit.php" class="common-btn">デッキ編集</a></li>
    </ul>
</div>

<!-- 画像を拡大表示するためのモーダル -->
<div id="imageModalSidebar" class="modalSidebar" style="display: none;">
    <span id="closeModalSidebar" class="close">&times;</span>
    <img class="modal-content-Sidebar" id="modalImageSidebar">
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
                const closeModalsidebar = document.getElementById('closeModalSidebar');
                const modalSidebar = document.getElementById('imageModal');
                closeModalsidebar.addEventListener('click', function() {
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