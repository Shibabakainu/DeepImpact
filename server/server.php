<?php
require dirname(__DIR__) . '/vendor/autoload.php';

use Ratchet\MessageComponentInterface;
use Ratchet\ConnectionInterface;

class Game{
    
    private $state;
    private $players;

    public function __construct($clients)
    {
        $this->state ='waiting';
        $this->players=[];
    }

    public function addPlayer($playerId){
        $this->players[$playerId]=['score'=>0];
    }
    
    public function removePlayer($playerId){
        unset($this->players[$playerId]);
    }
    
    public function startGame(){
        $this->state = 'playing';
        $this->broadcastState();
    }
    
    public function endGame(){
        $this->state ='ended';
        $this->broadcastState();
    }

    public function broadcastState(){
        $stateMessage = json_encode([
            'type' => 'state',
            'state' => $this->state,
            'players' => $this->players
        ]);

        foreach ($this->clients as $client) {
            $client->send($stateMessage);
        }
    }

    public function handleAction($playerId,$action)
    {
        if($this->state !== 'playing')
        {
            return;
        }
        switch ($action['type']){
            case 'draw_card';
            $this->drawCard($playerId);
            break;
        }
        $this->broadcastState();
    }

    private function drawCard($playerId){
        if(count($this->deck)===0){
            return;
        }
        $card = array_pop($this->deck);
        $this->players[$playerId]['hand'][]= $card;
        $this->players[$playerId]['score'] += $card; 
    }
}

class Chat implements MessageComponentInterface {
    protected $clients;

    public function __construct() {
        $this->clients = new \SplObjectStorage;
    }

    public function onOpen(ConnectionInterface $conn) {
        // 新しいクライアントが接続したときに呼び出されます
        $this->clients->attach($conn);
        echo "New connection! ({$conn->resourceId})\n";
    }

    public function onMessage(ConnectionInterface $from, $msg) {
        // メッセージを受信したときに呼び出されます
        foreach ($this->clients as $client) {
            if ($from !== $client) {
                // 自分以外のすべてのクライアントにメッセージを送信
                $client->send($msg);
            }
        }
    }

    public function onClose(ConnectionInterface $conn) {
        // クライアントが切断したときに呼び出されます
        $this->clients->detach($conn);
        echo "Connection {$conn->resourceId} has disconnected\n";
    }

    public function onError(ConnectionInterface $conn, \Exception $e) {
        // エラーが発生したときに呼び出されます
        echo "An error has occurred: {$e->getMessage()}\n";
        $conn->close();
    }
}

use Ratchet\Server\IoServer;
use Ratchet\Http\HttpServer;
use Ratchet\WebSocket\WsServer;

$server = IoServer::factory(
    new HttpServer(
        new WsServer(
            new Chat()
        )
    ),
    8080
);

$server->run();
