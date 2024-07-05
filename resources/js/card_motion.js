document.addEventListener('DOMContentLoaded', function() {
    const toggleButton = document.getElementById('toggle-cards');
    const cardContainer = document.getElementById('card-container');
    const buttonContainer = document.getElementById('button-container');

    if (toggleButton && cardContainer && buttonContainer) {
        toggleButton.addEventListener('click', function() {
            if (cardContainer.style.display === 'none') {
                cardContainer.style.display = 'flex';
                buttonContainer.style.marginTop = '200px'; // ボタンを上に移動
            } else {
                cardContainer.style.display = 'none';
                buttonContainer.style.marginTop = '200px'; // ボタンを下に移動
            }
        });
    } else {
        console.error('Cannot find toggle-cards button, card-container, or button-container');
    }
});

// カードを非表示にする関数
function hideCard(cardId) {
    const card = document.getElementById(cardId);
    if (card) {
        card.style.display = 'none';
    }
}
