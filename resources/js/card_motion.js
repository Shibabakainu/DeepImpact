document.addEventListener('DOMContentLoaded', function() {
    const cardContainer = document.getElementById('card-container');
    const selectedCardArea = document.getElementById('selected-card-area');
    let currentlySelectedCard = null;
    let previousParent = null;

    function updateCardVisibility(cardId, visibility) {
        fetch('update_card_visibility.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                cardId: cardId,
                visibility: visibility
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                console.log(`Card ${cardId} visibility updated to ${visibility}`);
            } else {
                console.error(`Failed to update visibility for card ${cardId}`);
            }
        })
        .catch(error => console.error('Error:', error));
    }

    cardContainer.addEventListener('click', function(e) {
        if (e.target.tagName.toLowerCase() === 'img') {
            const card = e.target.parentElement;
            const cardValue = card.getAttribute('data-value');

            if (card === currentlySelectedCard) {
                // 選択されているカードをクリックした場合、元の位置に戻す
                previousParent.appendChild(currentlySelectedCard);
                currentlySelectedCard.setAttribute('aria-selected', 'false');
                updateCardVisibility(cardValue, 2);
                currentlySelectedCard = null;
            } else {
                if (currentlySelectedCard) {
                    // 前の選択を元の位置に戻す
                    previousParent.appendChild(currentlySelectedCard);
                    currentlySelectedCard.setAttribute('aria-selected', 'false');
                    updateCardVisibility(currentlySelectedCard.getAttribute('data-value'), 2);
                }

                // 新しいカードを選択
                previousParent = card.parentElement;
                card.setAttribute('aria-selected', 'true');
                selectedCardArea.appendChild(card);
                card.style.position = 'relative';
                card.style.left = '';
                card.style.top = '';
                updateCardVisibility(cardValue, 3);

                currentlySelectedCard = card;
            }
        }
    });

    selectedCardArea.addEventListener('click', function(e) {
        if (e.target.tagName.toLowerCase() === 'img' && currentlySelectedCard) {
            // 選択されているカードをクリックした場合、元の位置に戻す
            previousParent.appendChild(currentlySelectedCard);
            currentlySelectedCard.setAttribute('aria-selected', 'false');
            updateCardVisibility(currentlySelectedCard.getAttribute('data-value'), 2);
            currentlySelectedCard = null;
        }
    });
});
