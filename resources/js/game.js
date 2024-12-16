document.addEventListener('DOMContentLoaded', function () {
    // BGMコントロール
    const bgm = document.getElementById('bgm');
    const bgmToggleBtn = document.getElementById('bgm-toggle-btn');
    const bgmIcon = document.getElementById('bgm-icon');
    let isBgmPlaying = false;

    // 初回クリックでミュート解除 (Chrome制限対応)
    document.body.addEventListener('click', () => {
        if (bgm) {
            bgm.muted = false;
            bgm.play().catch(console.error);
        }
    }, { once: true });

    // BGM再生/停止の切り替え
    if (bgmToggleBtn) {
        bgmToggleBtn.addEventListener('click', () => {
            if (isBgmPlaying) {
                bgm.pause();
                bgmIcon.textContent = '🔇';
            } else {
                bgm.play().catch(console.error);
                bgmIcon.textContent = '🔊';
            }
            isBgmPlaying = !isBgmPlaying;
        });
    }

    // ページを離れる前にBGM停止
    window.addEventListener('beforeunload', () => bgm?.pause());

    // カードホバー時の効果音
    const hoverSound = new Audio('/DeepImpact/bgm/03_ぷい.mp3');
    hoverSound.preload = 'auto';

    $(document).on('mouseenter', '.card', function () {
        hoverSound.currentTime = 0;
        hoverSound.play().catch(err => console.error("ホバー効果音エラー:", err));
    });

    // 手札と投票エリアの初期化
    const updateDrawnCards = () => {
        $.ajax({
            url: 'get_drawn_cards.php',
            method: 'GET',
            data: { room_id: roomId },
            dataType: 'json',
            success: function (response) {
                if (response.success) {
                    const drawArea = $('#drawed-card-area');
                    const voteArea = $('#vote-area');

                    drawArea.empty();
                    response.cards_unselected.forEach(card => {
                        drawArea.append(
                            `<div class="card" data-room-card-id="${card.room_card_id}">
                                <img src="../../images/${card.Image_path}" alt="${card.Card_name}">
                            </div>`
                        );
                    });

                    voteArea.empty();
                    response.cards_selected.forEach(card => {
                        voteArea.append(
                            `<div class="selected-card" data-room-card-id="${card.room_card_id}">
                                <img src="../../images/${card.Image_path}" alt="${card.Card_name}">
                            </div>`
                        );
                    });
                } else {
                    console.error('手札の取得に失敗:', response.message);
                }
            },
            error: function () {
                alert('手札の取得に失敗しました。');
            }
        });
    };

    // カードを引くボタンのクリックイベント
    $('#draw-cards').click(function () {
        $.ajax({
            url: 'draw_cards.php',
            method: 'POST',
            dataType: 'json',
            success: function (response) {
                if (response.success) {
                    updateDrawnCards();
                } else {
                    alert(response.message);
                }
            },
            error: function () {
                alert('カードを引く際にエラーが発生しました。');
            }
        });
    });

    // カード選択時のクリックイベント
    $(document).on("click", ".card", function () {
        const roomCardId = $(this).data("room-card-id");

        if (!roomCardId) {
            alert("カードIDが見つかりません。");
            return;
        }

        $.ajax({
            url: 'select_card.php',
            method: 'POST',
            data: { room_id: roomId, room_card_id: roomCardId },
            dataType: 'json',
            success: function (response) {
                if (response.success) {
                    $(`.card[data-room-card-id="${roomCardId}"]`).remove();
                    updateDrawnCards();
                } else {
                    alert(response.message);
                }
            },
            error: function () {
                alert("カード選択時にエラーが発生しました。");
            }
        });
    });

    // 投票エリアの更新
    const updateVoteArea = () => {
        $.ajax({
            url: 'get_votes.php',
            method: 'GET',
            data: { room_id: roomId },
            dataType: 'html',
            success: function (response) {
                $('#vote-area').html(response);
            },
            error: function () {
                alert('投票エリアの更新に失敗しました。');
            }
        });
    };

    // 投票処理
    $(document).on('click', '.selected-card', function () {
        const roomCardId = $(this).data('room-card-id');
        if (!roomCardId) {
            alert('カードIDが見つかりません。');
            return;
        }

        $.ajax({
            url: 'vote.php',
            method: 'POST',
            data: { room_id: roomId, room_card_id: roomCardId },
            dataType: 'json',
            success: function (response) {
                if (response.success) {
                    alert('投票が完了しました！');
                } else {
                    alert('投票に失敗しました: ' + response.message);
                }
            },
            error: function () {
                alert('投票中にエラーが発生しました。');
            }
        });
    });

    // ターン情報の表示
    const displayTurn = () => {
        $.ajax({
            url: `get_turn.php?room_id=${roomId}`,
            method: 'GET',
            dataType: 'json',
            success: function (data) {
                if (data.success) {
                    $('#turnDisplay').text("現在のターン： " + data.turn_number);
                } else {
                    console.error("ターン情報取得失敗");
                }
            },
            error: function () {
                console.error("ターン情報取得エラー");
            }
        });
    };

    // ターン更新のポーリング
    const pollVotingStatus = () => {
        setInterval(() => {
            $.ajax({
                url: 'checkVotingStatus.php',
                method: 'GET',
                data: { room_id: roomId },
                dataType: 'json',
                success: function (response) {
                    if (response.game_over) {
                        alert(response.message);
                        // 必要に応じてリダイレクトやアクション停止
                    } else if (response.votingComplete) {
                        updateVoteArea();
                        displayTurn();
                    }
                },
                error: function () {
                    console.error('投票状態チェックエラー');
                }
            });
        }, 3000);
    };

    // スコアボードの表示
    const loadScoreboard = () => {
        $.ajax({
            url: 'getScoreboard.php',
            method: 'GET',
            data: { room_id: roomId },
            dataType: 'json',
            success: function (response) {
                $('.scoreboard').html(response.scoreboard);
            },
            error: function () {
                console.error('スコアボードの取得エラー');
            }
        });
    };

    // 初期化
    displayTurn();
    updateDrawnCards();
    loadScoreboard();
    pollVotingStatus();

    // スコアボードの自動更新
    setInterval(loadScoreboard, 5000);
});
