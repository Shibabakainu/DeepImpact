<style>
    .tutorial-container {
        min-height: 100vh;
        padding: 40px;
    }

    .list {
        display: grid;
        row-gap: 20px;
        padding: 20px;
    }

    .details {
        background-color: white;
        margin-bottom: 10px;
    }

    .answerInner {
        padding: 0 20px 20px;
        text-align: center;
        /* 画像を中央に配置 */
        font-size: 24px;
    }

    .answerInner img {
        display: inline-block;
        /* 中央揃えのためにインライン要素に変更 */
        margin-top: 10px;
        cursor: pointer;
        max-width: 100%;
        height: auto;
    }

    .summary {
        font-size: 24px;
        background-color: lightblue;
        cursor: pointer;
        font-weight: bold;
        padding: 20px;
        border-radius: 7px;
    }

    .answer {
        overflow: hidden;
    }

    /* モーダル用スタイル */
    .modal {
        display: none;
        position: fixed;
        z-index: 1000;
        left: 0;
        top: 0;
        width: 100%;
        height: 100%;
        background-color: rgba(0, 0, 0, 0.8);
        justify-content: center;
        align-items: center;
    }

    .modal img {
        max-width: 90%;
        max-height: 90%;
        margin: auto;
        display: block;
    }
</style>

<div class="tutorial-container">
    <details class="details">
        <summary class="summary">ゲーム概要</summary>
        <div class="answer">
            <div class="answerInner">
                <br><u><em>最大6人</u></em>による役に当てはまるカードを自分の手札の中で考えて出し合い<br>全員の投票で配役を決め物語の印象が遊ぶたびに変わっていくゲームです。
            </div>
        </div>
    </details>
    <br />
    <details class="details">
        <summary class="summary">ゲーム進行</summary>
        <div class="answer">
            <div class="answerInner">
                ※画像クリックで拡大表示できます※<br>
                <img src="/DeepImpact/images/tutorial_1.jpg" alt="説明画像" style="max-width: 40%; height: auto;" class="clickableImage"><br>
                上の画像がゲーム開始時の画面です。<br>
                <u>Draw Cards</u>ボタンでカードを引くことができます。<br>
                <br>
                <img src="/DeepImpact/images/tutorial_2.jpg" alt="説明画像" style="max-width: 40%; height: auto;" class="clickableImage"><br>
                カードを手札に加えたら画面上部の物語を確認し、キーワードに適していると思うカードをクリックし提出しましょう。
            </div>
        </div>
    </details>
    <br />
    <details class="details">
        <summary class="summary">質問</summary>
        <div class="answer">
            <div class="answerInner">切手を舐めると２キロカロリーを摂取できる。</div>
        </div>
    </details>
    <br />
    <details class="details">
        <summary class="summary">プライバシーポリシー</summary>
        <div class="answer">
            <br>
            <div class="answerInner">StoryTellerは運営はサービス利用者の個人情報を以下のとおりに取り扱います。</div>
            <br>
            <div class="answerInner"><b>利用目的</b></div>
            <br>
            <div class="answerInner">利用者の個人情報はサービスの提供及びお問い合わせ対応のために利用されます。</div>
            <br>
            <div class="answerInner"><b>開示</b></div>
            <br>
            <div class="answerInner">利用者の同意があるか法令に基づき開示が必要な場合を除き、個人情報は第三者に提供されません。</div>
            <br>

        </div>
    </details>

</div>

<!-- モーダル -->
<div id="imageModal" class="modal">
    <span class="close-modal" id="closeModal"></span>
    <img id="modalImage" src="">
</div>

<script>
    // tutorial.php内の画像クリック処理を再定義
    const clickableImages = document.querySelectorAll('.clickableImage');

    clickableImages.forEach(image => {
        image.addEventListener('click', function() {
            const modal = document.getElementById('imageModal');
            const modalImage = document.getElementById('modalImage');
            modal.style.display = 'flex'; // モーダルを表示
            modalImage.src = this.src; // クリックした画像のsrcをモーダルに設定
        });
    });

    // モーダルを閉じる処理
    closeModal.addEventListener('click', function() {
        modal.style.display = 'none';
    });

    // モーダルの外側をクリックして閉じる
    modal.addEventListener('click', function(e) {
        if (e.target === modal) {
            modal.style.display = 'none';
        }
    });
</script>