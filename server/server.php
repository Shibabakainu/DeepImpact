<?php
require dirname(__DIR__) . '/vendor/autoload.php';

use Ratchet\MessageComponentInterface;
use Ratchet\ConnectionInterface;

class StorytellerGame implements MessageComponentInterface {
    protected $clients;
    protected $gameState;

    public function __construct() {
        $this->clients = new \SplObjectStorage;
        $this->gameState = [
            'players' => [],
            'deck' => $this->initializeDeck(),
            'currentRound' => [],
            'votes' => []
        ];
    }

    public function onOpen(ConnectionInterface $conn) {
        $this->clients->attach($conn);
        echo "New connection! ({$conn->resourceId})\n";
    }

    public function onMessage(ConnectionInterface $from, $msg) {
        $data = json_decode($msg, true);

        switch ($data['type']) {
            case 'join':
                $this->handleJoin($from, $data['username']);
                break;
            case 'draw_card':
                $this->handleDrawCard($from);
                break;
            case 'play_card':
                $this->handlePlayCard($from, $data['card']);
                break;
            case 'vote':
                $this->handleVote($from, $data['card']);
                break;
        }
    }

    public function onClose(ConnectionInterface $conn) {
        // クライアントが切断したときに呼び出されます
        unset($this->gameState['players'][$conn->resourceId]);
        $this->clients->detach($conn);
        $this->broadcastPlayerList(); // プレイヤーリストを更新して送信
        echo "Connection {$conn->resourceId} has disconnected\n";

        $this->broadcastPlayerList();
    }

    public function onError(ConnectionInterface $conn, \Exception $e) {
        echo "An error has occurred: {$e->getMessage()}\n";
        $conn->close();
    }

    protected function handleJoin(ConnectionInterface $conn, $username) {
        // 新しいプレイヤーがゲームに参加
        $this->gameState['players'][$conn->resourceId] = [
            'username' => $username,
            'hand' => []
        ];
        $this->broadcastPlayerList(); // プレイヤーリストを送信
    }


    protected function handleDrawCard(ConnectionInterface $conn) {
        // プレイヤーがカードを引く
        if (!empty($this->gameState['deck'])) {
            $card = array_pop($this->gameState['deck']);
            $playerId = $conn->resourceId;
            
            // プレイヤーの手札を更新
            $this->gameState['players'][$playerId]['hand'][] = $card;
            
            // 手札をクライアントに送信
            $conn->send(json_encode([
                'type' => 'update_hand',
                'hand' => $this->gameState['players'][$playerId]['hand']
            ]));
            
            // プレイヤーリストを全クライアントに送信
            $this->broadcastPlayerList();
        }
    }

    protected function handlePlayCard(ConnectionInterface $conn, $card) {
        // プレイヤーがカードを出す
        $this->gameState['currentRound'][$conn->resourceId] = $card;
        if (count($this->gameState['currentRound']) == count($this->gameState['players'])) {
            $this->broadcastGameState();
        }
    }

    protected function handleVote(ConnectionInterface $conn, $card) {
        // プレイヤーが投票する
        $this->gameState['votes'][$conn->resourceId] = $card;
        if (count($this->gameState['votes']) == count($this->gameState['players'])) {
            $this->calculateResults();
        }
    }

    protected function calculateResults() {
        // 投票結果を計算する
        $this->broadcastGameState();
        // 次のラウンドの準備
        $this->gameState['currentRound'] = [];
        $this->gameState['votes'] = [];
    }

    protected function broadcastGameState() {
        foreach ($this->clients as $client) {
            $client->send(json_encode([
                'type' => 'game_state',
                'state' => $this->gameState
            ]));
        }
    }

    protected function broadcastPlayerList() {
        // 参加しているプレイヤーのリストを全クライアントに送信
        $playerList = [];
        foreach ($this->gameState['players'] as $player) {
            $playerList[] = $player['username'];
        }

        foreach ($this->clients as $client) {
            $client->send(json_encode([
                'type' => 'player_list',
                'players' => $playerList
            ]));
        }
    }

    protected function initializeDeck() {
        // デッキを初期化する
        $suits = ['Hearts', 'Diamonds', 'Clubs', 'Spades'];
        $ranks = ['2', '3', '4', '5', '6', '7', '8', '9', '10', 'J', 'Q', 'K', 'A'];

        $deck = [];
        
        // デッキを作成
        foreach ($suits as $suit) {
            foreach ($ranks as $rank) {
                $deck[] = $rank . ' of ' . $suit;
            }
        }

        // デッキをシャッフル
        shuffle($deck);

        return $deck;
    
    }
}

use Ratchet\Server\IoServer;
use Ratchet\Http\HttpServer;
use Ratchet\WebSocket\WsServer;

$server = IoServer::factory(
    new HttpServer(
        new WsServer(
            new StorytellerGame()
        )
    ),
    8080
);

$server->run();
?>
