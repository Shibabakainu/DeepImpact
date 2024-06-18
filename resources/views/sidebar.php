<div id="sidebar" class="sidebar" style="background-color: #fae6b1;">
    <ul>
        <li><a href="/deepimpact/resources/views/index.php" class="common-btn">ホームに戻る</a></li>
        <li><button id="sidebar-click-btn" class="common-btn">ルール</button>
            <div id="sidebar-popup-wrapper">
                <div id="sidebar-popup-inside">
                    <div id="sidebar-close">X</div>
                    <div class="text2">
                        <h2>Story Teller</h2>
                        <p>ルールです。<br>改行</p>
                    </div>
                </div>
            </div>
        </li>
        <li><a href="/deepimpact/resources/views/login/profile.php" class="common-btn">プロフィール</a></li>
        <li><a href="/deepimpact/resources/views/logout.php" class="common-btn">ログアウト</a></li>
    </ul>
</div>

<script>
    const sidebarClickBtn = document.getElementById('sidebar-click-btn');
    const sidebarPopupWrapper = document.getElementById('sidebar-popup-wrapper');
    const sidebarClose = document.getElementById('sidebar-close');

    // ボタンをクリックしたときにポップアップを表示させる
    sidebarClickBtn.addEventListener('click', () => {
        sidebarPopupWrapper.style.display = "block";
    });

    // ポップアップの外側又は「x」のマークをクリックしたときポップアップを閉じる
    sidebarPopupWrapper.addEventListener('click', e => {
        if (e.target.id === sidebarPopupWrapper.id || e.target.id === sidebarClose.id) {
            sidebarPopupWrapper.style.display = 'none';
        }
    });
</script>