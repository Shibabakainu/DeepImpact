// 最初のクリックでミュート解除 (Chrome制限対応)
document.body.addEventListener('click', () => {
    bgm.muted = false;
    bgm.play().catch(console.error);
}, {
    once: true
}); // このイベントは一度だけ実行

const hoverSound = new Audio('/DeepImpact/bgm/03_ぷい.mp3')
    hoverSound.preload = 'auto';
    $(document).on('mouseenter', '.card', function() {
        hoverSound.currentTime = 0; // 効果音をリセットして最初から再生
        hoverSound.play().catch(error => console.error("ホバーサウンド再生に失敗:", error));
    });

const context = new AudioContext();
// Setup an audio graph with AudioNodes and schedule playback.

// Resume AudioContext playback when user clicks a button on the page.
document.querySelector('button').addEventListener('click', function() {
    context.resume().then(() => {
        console.log('AudioContext playback resumed successfully');
    });
});

// DOMの読み込みが完了したときに実行される処理
document.addEventListener('DOMContentLoaded', function() {
    const bgm = document.getElementById('bgm');
    const bgmToggleBtn = document.getElementById('bgm-toggle-btn');
    const bgmIcon = document.getElementById('bgm-icon');
    let isPlaying = false;

    // ボタンがクリックされたときのイベントハンドラを定義
    bgmToggleBtn.addEventListener('click', function() {
        if (isPlaying) {
            // 再生中ならBGMを一時停止
            bgm.pause();
            bgmIcon.textContent = '🔇'; // アイコンをミュートのものに変更
        } else {
            // 停止中ならBGMを再生
            bgm.play();
            bgmIcon.textContent = '🔊'; // アイコンをスピーカーのものに変更
        }
        isPlaying = !isPlaying; // フラグを反転（再生⇔停止を切り替え）
    });

    // ユーザーがページを離れる前に音楽を停止する処理
    window.addEventListener('beforeunload', () => {
        bgm.pause(); // ページが閉じられる前にBGMを停止
    });

    // 1秒後にボタンを自動的にクリック
    setTimeout(function() {
        bgmToggleBtn.click(); // ここでボタンがクリックされる
    }, 2000); // 1000ミリ秒 = 1秒
});

window.onload = function() {
    // Automatically check if there are already drawn cards
    updateDrawnCards(); // Call function to update drawn cards display
    var bgm = document.getElementById('bgm');
};

// Function to update drawn cards (on-hand) and vote area on load
function updateDrawnCards() {
    // Fetch drawn cards from the server
    $.ajax({
        url: 'get_drawn_cards.php', // Create this script to retrieve drawn cards for the current user
        method: 'GET',
        data: {
            room_id: roomId
        },
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                // Update on-hand (unselected) cards
                $('#drawed-card-area').empty(); // Clear existing cards
                response.cards_unselected.forEach(function(card) {
                    $('#drawed-card-area').append(
                        '<div class="card" data-room-card-id="' + card.room_card_id + '">' +
                        '<img src="../../images/' + card.Image_path + '" alt="' + card.Card_name + '">' +
                        '</div>'
                    );
                });

                // Update vote area with selected cards
                $('#vote-area').empty(); // Clear existing cards
                response.cards_selected.forEach(function(card) {
                    $('#vote-area').append(
                        '<div class="selected-card" data-room-card-id="' + card.room_card_id + '">' +
                        '<img src="../../images/' + card.Image_path + '" alt="' + card.Card_name + '">' +
                        '</div>'
                    );
                });
            } else {
                console.error('Failed to retrieve drawn cards: ' + response.message);
            }
        },
        error: function(jqXHR, textStatus, errorThrown) {
            alert('Error retrieving drawn cards: ' + textStatus + ' ' + errorThrown);
        }
    });
}

// URLからroom_idを取得する関数
function getRoomIdFromUrl() {
    const params = new URLSearchParams(window.location.search);
    return params.get('room_id');
}

const roomId = getRoomIdFromUrl(); // URLからroom_idを取得

// Click event for drawing cards
$(document).ready(function() {
    $("#draw-cards").click(function() {
        $.ajax({
            url: 'draw_cards.php',
            method: 'POST',
            dataType: 'json',
            success: function(response) {
                $('#drawed-card-area').empty(); // 既存のカードをクリア

                if (response.success) {
                    response.cards.forEach(function(card) {
                        $('#drawed-card-area').append(
                            '<div class="card" data-room-card-id="' + card.room_card_id + '">' +
                            '<img src="../../images/' + card.Image_path + '" alt="' + card.Card_name + '">' +
                            '</div>'
                        );
                    });
                } else {
                    // エラーメッセージのポップアップを表示
                    showPopup(response.message);
                }
            },
            error: function() {
                showPopup("カードを引く際にエラーが発生しました。");
            }
        });
    });

    // カード選択時のクリックイベント
    $(document).on("click", ".card", function() {
        var roomCardId = $(this).data("room-card-id");

        if (!roomCardId) {
            showPopup("カードIDが見つかりません。");
            return;
        }

        // Click event for selecting cards
        $.ajax({
            url: 'select_card.php',
            method: 'POST',
            data: {
                room_id: roomId,
                room_card_id: roomCardId
            },
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    showPopup(response.message);

                    // 選択済みクラスを追加
                    $(".card[data-room-card-id='" + roomCardId + "']").addClass('selected');

                    // 手札エリアから選択済みカードを削除
                    $(".card[data-room-card-id='" + roomCardId + "']").remove();

                    // 投票エリアを更新
                    updateVoteArea();
                } else {
                    showPopup(response.message);
                }
            },
            error: function() {
                showPopup("カードの選択時にエラーが発生しました。");
            }
        });
    });

    // Function to show popup and hide it after 2 seconds
    function showPopup(message) {
        $('#popup-message').text(message).fadeIn();
        setTimeout(function() {
            $('#popup-message').fadeOut();
        }, 2000); // Hide after 2 seconds
    }
});

// Function to fetch and update the vote area
function updateVoteArea() {
    $.ajax({
        url: 'get_votes.php',
        method: 'GET',
        data: {
            room_id: roomId
        },
        dataType: 'html',
        success: function(response) {
            $('#vote-area').empty(); // Clear previous content
            $('#vote-area').append(response); // Add the new content
        },
        error: function() {
            alert('投票エリアの更新に失敗しました。');
        }
    });
}

// Voting logic
$(document).on('click', '.selected-card', function() {
    var roomCardId = $(this).data('room-card-id'); // Capture room_card_id

    if (!roomCardId) {
        alert('Room Card ID is missing!');
        return; // Ensure we have a valid roomCardId
    }

    if (!roomId) {
        alert('Room ID is missing!');
        return; // Ensure we have a valid roomId
    }

    $.ajax({
        url: 'vote.php',
        method: 'POST',
        data: {
            room_card_id: roomCardId, // Send room_card_id
            room_id: roomId // Send room_id
        },
        dataType: 'json', // Expect JSON response
        success: function(response) {
            if (response.status === 'success') {
                alert('投票が完了しました！');
            } else {
                alert('投票に失敗しました: ' + response.message);
            }
        },
        error: function(xhr, status, error) {
            console.error("Response received:", xhr.responseText);
            console.error("エラーが発生しました:", status, error);
            alert('投票中にエラーが発生しました。再度お試しください。');
        }
    });
});

//アップデートしたターンを表示する
// JavaScript function to display and update the current turn
function displayTurn() {
    // Send AJAX request to get the current turn from the server
    fetch(`get_turn.php?room_id=${roomId}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                document.getElementById("turnDisplay").innerText = "現在のターン： " + data.turn_number;
            } else {
                console.error("Failed to fetch turn information.");
            }
        })
        .catch(error => console.error("Error:", error));
}

// Call displayTurn initially to show the current turn
displayTurn();

// Function to be called at the end of each turn to update the turn display
function updateTurn() {
    displayTurn(); // Refresh the turn display
}

//投票が終わった後の処理
function pollVotingStatus() {
    const roomId = getRoomIdFromUrl();

    setInterval(() => {
        $.ajax({
            url: 'checkVotingStatus.php',
            method: 'GET',
            data: {
                room_id: roomId
            },
            dataType: 'json',
            success: function(response) {
                if (response.game_over) {
                    alert(response.message);
                    // Additional logic for game over, like redirecting or disabling actions
                    // Disable voting and other game actions if needed
                } else {
                    // Update the turn display and score as usual
                    updateTurn();

                    if (response.votingComplete) {
                        // If voting is complete, update the scoreboard
                        $('.scoreboard').html(response.scoreboard);
                    }
                }
            },
            error: function(jqXHR, textStatus, errorThrown) {
                console.error("Error checking voting status: ", textStatus, errorThrown);
                console.log("Response text: ", jqXHR.responseText); // Log detailed error response
            }
        });
    }, 3000); // Poll every 3 seconds
}

// Call pollVotingStatus on page load to start polling
pollVotingStatus();

$(document).ready(function() {
    // Fetch and display the scoreboard on page load
    function loadScoreboard() {
        $.ajax({
            url: 'getScoreboard.php',
            method: 'GET',
            data: { room_id: roomId },
            dataType: 'json',
            success: function(response) {
                $('.scoreboard').html(response.scoreboard);
            },
            error: function(jqXHR, textStatus, errorThrown) {
                console.error("Error loading scoreboard: ", textStatus, errorThrown);
                console.log("Response text:", jqXHR.responseText); // Log the full response
            }
        });
    }

    // Initial load on page refresh
    loadScoreboard();

    // Optional: reload the scoreboard every few seconds if you want it to auto-refresh
    setInterval(loadScoreboard, 5000);
});
