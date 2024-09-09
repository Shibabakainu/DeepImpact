<div id="sidebar" class="sidebar" style="background-color: #fae6b1;">
    <ul>
        <li class="btn"><a href="/DeepImpact/resources/views/index.php" class="common-btn">ホームに戻る</a></li>
        <li class="btn"><button id="sidebar-click-btn" class="common-btn">ルール</button>
            <div id="sidebar-popup-wrapper">
                <div id="sidebar-popup-inside">
                    <div class="text2">
                        <div id="sidebar-close">X</div>
                        <p>※注意事項※</p>
                        <ul>
                            <li>ゲーム推奨プレイ人数は6人となっています。</li>
                        </ul>
                        <p>ゲーム開始時</p>
                        <ul class="rule">
                            <li class="a">各プレイヤーに5枚のカードを配ります。</li>
                        </ul>
                        <p>カードの提出</p>
                        <ul class="rule">
                            <li class="a">物語を確認し、自分の手札から物語のフレーズに合うと思うカードを1枚選択し、待機します。</li>
                            <li class="a">全てのプレイヤーが選び終えると、画面中央に選ばれたカードが表示されます。</li>
                        </ul>
                        <p>投票</p>
                        <ul class="rule">
                            <li class="a">各プレイヤーは、物語のフレーズに1番あっていると思うカードを選び、投票することができます。</li>
                            <li class="a">注意として、自身が提出したカードに投票することはできません。</li>
                        </ul>
                        <p>得点</p>
                        <ul class="rule">
                            <li class="a">投票が入ったカードを出したプレイヤーは、投票1つにつき、+1点を獲得します。</li>
                            <li class="a">1番票を集めたカードに、投票をしていた場合には投票者にも+1点を獲得します。</li>
                        </ul>
                        <p>ラウンド終了</p>
                        <ul class="rule">
                            <li class="a">各プレイヤーは新しいカードを1枚手に入れ、手札が5枚に戻ります。</li>
                        </ul>
                        <p>ゲーム終了</p>
                        <ul class="rule">
                            <li class="a">物語の決められたチャプター(ターン)が全て終えると、ゲーム終了です。</li>
                            <li class="a">最も得点の多いプレイヤーの勝利となります。</li>
                        </ul>

                    </div>
                </div>
            </div>
        </li>
        <li class="btn"><a href="/DeepImpact/resources/views/login/profile.php" class="common-btn">プロフィール</a></li>
        <li class="btn"><a href="/DeepImpact/resources/views/friend.php" class="common-btn">フレンド</a></li>
        <li class="btn"><a href="/DeepImpact/resources/views/logout.php" class="common-btn">ログアウト</a></li>
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