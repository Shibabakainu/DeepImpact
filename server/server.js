//基本的にserver.jsはクライアントでユーザーがボタンを押したときなどにその処理をするためのところです。
//game.phpで手札にあるカードを出そうとするとsocket.emit()みたいものが記述されてあると思いますそれがクライアントからサーバー側に情報を送るコードになります
//そして、送られてきた情報をserver.jsで適切に処理し、手札の更新やスコアの加算などがあった場合はさっきの逆でサーバーからクライアントに情報を渡します
//そのときに使われるコードがio.to(roomID).emit()です。これを使うと引数に設定されているものがクライアントに送信され、game.phpやroom_searchで使えるようになります

const express = require("express");
const https = require("https");
const { Server } = require("socket.io");
const cors = require("cors");
const fs =require('fs');
const mysql = require("mysql");

const options = {
  key: fs.readFileSync("/etc/letsencrypt/live/storyteller.help/privkey.pem"),
  cert: fs.readFileSync("/etc/letsencrypt/live/storyteller.help/fullchain.pem"),
  ca: fs.readFileSync("/etc/letsencrypt/live/storyteller.help/chain.pem"),
};

// サーバーのセットアップ
const app = express();
app.use(cors());
const server = https.createServer(options, app);
const io = new Server(server, {
  cors: {
    origin: "*",
    methods: ["GET", "POST"],
  },
});

const db = mysql.createConnection({
  //host: "192.168.3.79",
  host: "49.212.166.241",
  user: "thread",
  password: "PassWord1412%",
  database: "storyteller",
});

db.connect((err) => {
  if (err) throw err;
  console.log("mysql connected");
});

const room_players = {
  115: ["alice", "bob"],
  116: ["charlie", "david"],
  114: ["eve", "frank"],
};

app.use(express.urlencoded({ extended: true }));
app.use(express.json());

app.post("/server", (req, res) => {
  const roomId = req.body.room_id;
  if (room_players[roomId]) {
    res.json({ players: room_players[roomId] });
  } else {
    res.json({ error: "room not found" });
  }
});

const rooms = {};
const players = {};


function initializeDeck(callback) {

  let deck =[];
  loadCardsFromDB((cards) => {
    if(cards.length === 0){
      console.error("カードがデータベースに登録されていません");
      return;
    }
  
  for(let i = cards.length -1; i>0; i--){
    const j= Math.floor(Math.random() * (i+1));
    [cards[i], cards[j]] = [cards[j], cards[i]];
  }

  deck.push(...cards);

  callback(deck);
  console.log('山札を初期化しました');
});
}

function loadCardsFromDB(callback){
  db.query("select * from Card",(err,results) => {
    if(err){
      console.error("カードデータの読み込みエラー",err);
      callback([]);
    }else{
     
      const cards = results.map((row) => ({ id: row.Card_id, name: row.Card_name, image: row.Image_path}));
      callback(cards);    
    }
  });
}

// WebSocket 接続時の処理
io.on("connection", (socket) => {
  console.log("New connection:", socket.id);
  
  let votes={};
  let voters ={};
  let playedCards={};
  let playerPoints=[];
  let story;
  
  // ストーリーリストを定義（ラウンドごと）
const stories = [
  "昔々、平和な国があり、その国は緑豊かな土地と、穏やかな人々に恵まれていました。しかし魔王が現れ軍勢を率いて国を支配しまし。魔王は強力な魔法が使え、心臓が3つあり、国は恐怖に包まれました。人々は魔王に立ち向かう勇者が現れるのを待ち望んでいました。そんな時、小さな町に住むが立ち上がりました。正義感の強い若い戦士",
  "正義感の強い若い戦士は魔王を倒しに行こうと決心しました。しかし３つの心臓と軍勢相手に一人で行くのはあまりにも無謀だと思いました。それに３つの心臓はそれぞれ火と水と風の剣でないと効果がないことが分かりその剣の持ち主を探しに行きました。まず火の洞窟へ持ち主に会いに行きました。火の剣の持ち主はすごく協力的で体中に傷があり鋭い目をしていました。",
  "次に水の剣の持ち主に会いに行きました。水の剣の持ち主は協力してくれたものの愛想の悪い面倒くさがりの性格でした。",
  "最後に風の剣の持ち主に会いに行きました。風の剣の持ち主は警戒心が強く目力も強い背の高い力持ちでした",
  "四人は準備を整えて魔王を倒しにいきました。待ち構えていた軍勢を倒し魔王の部屋につきました。そこにいたのは背の低い威圧感のある強そうな魔王でした。",
  "壮絶な戦いの末、勇者たちは魔王を倒し、国に平和を取り戻しました。",
];


  socket.on("reconnectWithUserId", (data) => {
    //socket.ioのしようとしてページをリロードしたり、移動すると一度切断→再接続となります　このときに困るのがsocketIDという各々のクライアントを識別するために使用する文字列が変更されますので
    //ここでルームに参加するときにルームオブジェクトに保存しているuserIdと照合して合致するものがあれば保存されているルームのIDと古いsocketIDを新規のものに上書きしているというコードです
    const userId = data.userId;

    let reconnectedRoomId = null;
    for (const roomId in rooms) {
      const player = rooms[roomId].players.find((p) => p.userId === userId);
      if (player) {
        reconnectedRoomId = roomId;
        player.socketId = socket.id;
        break;
      }
    }

    if (reconnectedRoomId) {
      socket.join(reconnectedRoomId);
      console.log(
        `プレイヤー${userId}がルーム${reconnectedRoomId}に再接続しました`
      );
      console.log(rooms[reconnectedRoomId].players);
      io.to(reconnectedRoomId).emit("playerjoined", {
        players: rooms[reconnectedRoomId].players,
      });
      socket.emit("reconnectSuccess", { roomId: reconnectedRoomId });
    } else {
      console.log(`再接続できるルームが見つかりませんでした:${userId}`);
      socket.emit("reconnectError", {
        message: "再接続できるルームが見つかりません",
      });
    }
    players[userId] = { userId: userId, socketId: socket.id };
  });

  socket.on("createRoom", (data) => {
    //このコードは名前の通りルームを作るためのもので、クライアントから送られてきた名前や合言葉などをルームオブジェクトと呼ばれるオブジェクトに挿入しています
    //このオブジェクトという概念はroomIdを使って区分することができます　例えばrooms←これがオブジェクトこれをrooms[roomId]とするとroomID番のroomsの要素にアクセスすることができます
    //このroomsオブジェクトの中にルームに属しているプレイヤーの情報やデッキ、ラウンドの情報が入っています
    //ここでは、作成するために*2の部分でroomID番のオブジェクトを初期化している *3の部分でホストとなるユーザーをroomId番のルームのplayersという要素に挿入していますこの部分は下記のjoinroomでも同じことをしています
    console.log("create room");
    const { roomName, setting, maxPlayers, hostUserId } = data;

    if (!hostUserId) {
      console.error("host id is null");
      socket.emit("createRoomError", { message: "ホストIDが無効" });
      return;
    }
    const sql =
      "INSERT INTO rooms(room_name, host_id, max_players) VALUES (?,?,?)";
    db.query(sql, [roomName, hostUserId, maxPlayers], (err, result) => {
      if (err) {
        console.error("Error creating room", err);
        socket.emit("roomCreationError", "ルーム作成に失敗しました");
        return;
      }
      const roomId = result.insertId;
      const passwordHash = require("bcrypt").hashSync(setting, 10);

      const sqlPassword =
        "INSERT INTO room_passwords (room_id, password_hash) VALUES (?,?)";
      db.query(sqlPassword, [roomId, passwordHash], (err) => {
        if (err) {
          console.error("Error inserting password", err);
          socket.emit("roomCreationError", "合言葉設定に失敗しました");
          return;
        }
        rooms[roomId] = { // *2
          roomName: roomName,
          password: setting,
          players: [],
          deck: [],
          currentTurn: 0,
          table: [],
          playedCards: [],
          cardPlayedBy : [],
          votes:{},
          round:1,
          currentPlayers: 0,
          votedPlayers:{},
          maxPlayers: maxPlayers,
        };
        players[roomId] = [];

        players[roomId].push({//*3
          userId: hostUserId,
          socketId: socket.id,
          hand: [],
          roomId: roomId,
          isHost: true,
        });

        

        console.log("ルームが作成されました");
        socket.emit("roomCreated", { roomId });
      });
    });
  });

  // プレイヤーがルームに参加した場合
  socket.on("joinRoom", (data) => {
    //ここではroom_searchからルームに参加をする人のためのコードです、やっていることは上記のCreateroomと同じです。
    //条件分岐ですでに入っている人や合言葉が違う人などは事前にreturnで弾いています
    const { roomId, userId, userName, password, hostId } = data;
    console.log("Data received for joinRoom:", data);

    if (rooms[roomId]) {
      const room = rooms[roomId];
      if (room.password && room.password !== password) {
        socket.emit("joinRoomError", { message: "パスワードが間違っています" });
        return;
      }

      if (room.players.includes(socket.id)) {
        socket.emit("joinRoomError", {
          message: "すでにルームに参加しています",
        });
        return;
      }

      const existingPlayer = room.players.find(
        (player) => player.userId === userId
      );
      if (existingPlayer) {
        existingPlayer.socketId = socket.id;
        socket.join(roomId);
        console.log(`プレイヤー${userName}が再接続しました`);
        socket.emit("joinRoomSuccess", { roomId });
        io.to(roomId).emit("playerjoined", { userId: existingPlayer.userId });
        return;
      }

      if (room.currentPlayers > room.maxPlayers) {
        socket.emit("joinRoomError", { message: "ルームが満室です" });
        return;
      }

      const player = {
        userId: userId,
        socketId: socket.id,
        name: userName,
        hand: [],
        score: 0,
        roomId: roomId,
      };

      room.players.push(player); // ユーザーをルームに追加
      socket.join(roomId);
      console.log(`socket ${socket.id} is in rooms`, socket.rooms);
      console.log(io.sockets.adapter.rooms.get(roomId));
      console.log(`プレイヤー ${userName} がルーム ${roomId} に参加しました`);
      room.currentPlayers += 1;

      players[roomId].push({
        userId: userId,
        socketId: socket.id,
        userName: userName,
        hand: [],
        score: 0,
      });

      console.log("ルーム内のプレーヤーを更新しました");
      socket.emit("joinRoomSuccess", { roomId });
      socket.to(roomId).emit("playerjoined", { players: room.players });
    } else {
      socket.emit("joinRoomError", { message: "ルームがありません" });
      console.log("current rooms", rooms);
    }
  });

  socket.on("playCard", (data) => {
    //ここではgame.phpでクライアントがカードを出すときに使用するコードです

    const { roomId, cardId } = data;
    const room = rooms[roomId];

    if (!room) {
      console.error(`ルーム ${roomId} が存在しません`);
      return;
    }

    // プレイヤーが手札からカードを出す処理
    const player = room.players.find((p) => p.socketId === socket.id);
    if (!player) {
      console.error("プレイヤーが見つかりません");
      return;
    }

    console.log(player.hands);
    console.log(cardId);

    // 出されたカードを手札から取り除く
    const cardIndex = player.hands.findIndex(
      (card) => card.id.trim() === String(cardId).trim()
    );
    if (cardIndex === -1) {
      console.error("そのカードは手札に存在しません");
      return;
    }

    const card = player.hands.splice(cardIndex, 1)[0]; 
    const socketId =player.socketId;
    const hand = player.hands;
    io.to(socketId).emit("dealCards", { cards: hand });

    const userId =player.userId;

    // 場に出すカードを保存（例えば、room場に置くカードリストを管理する）
    room.playedCards.push(card);
    console.log(card);
    if(!room.cardPlayedBy)room.cardPlayedBy = [];
    room.cardPlayedBy.push(player.userId);

    // すべてのプレイヤーに場に出されたカードを通知
    io.to(roomId).emit("cardPlayed", { playerId: userId, card: card });

    if(room.cardPlayedBy.length === room.players.length){
      console.log('全プレイヤーがカードを提出しました');
      io.to(roomId).emit('votingReady',{playedCards: room.playedCards});
    }
  });

  socket.on("leaveRoom", (roomId) => {
    for (const id in rooms) {
      const playerIndex = rooms[id].players.findIndex(
        (p) => p.socketId === socket.id
      );
      if (playerIndex !== -1) {
        const player = rooms[id].players[playerIndex];
        rooms[id].players.splice(playerIndex,1);
        console.log(rooms[id].players.length);
        if (rooms[id].players.length === 0) {

          delete rooms[id];
          deleteRoomFromDB(id);
          console.log('0人になったため削除します');
          
        } else {
          io.to(id).emit("playerleft", { players: rooms[id].players });
        }
        socket.leave(id);
        console.log(
          `プレイヤー${player.userId}がルーム${id}から退室されました`
        );
        break;
      }
    }
  });

  socket.on('entryGame',(data) => {
    console.log('entry');
    const roomId = data.roomId;
    console.log(data.roomId);
    console.log(roomId);
    io.to(roomId).emit('gameStarted',{roomId:roomId});
  })

  socket.on("startGame", ({ roomId }) => {
    const room = rooms[roomId];
    const story =stories[0];

    io.to(roomId).emit("storyDisplay",{story:story});
    io.to(roomId).emit('playerJoined',{players:room.players});

    if (!rooms[roomId]) {
      console.error("Room ${roomId} does not exist");
      return;
    }
    distributeCardsToRoomPlayers(roomId);
    console.log("complete");
  });

  socket.on("disconnect", () => {
    console.log("User disconnected:", socket.id);
    for (const roomId in rooms) {
      const playerIndex = rooms[roomId].players.findIndex(
        (p) => p.socketId === socket.id
      );
      if (playerIndex !== -1) {
        const player = rooms[roomId].players[playerIndex];
        player.socketId = null;
        console.log(
          `プレイヤー${player.userId}がルーム${roomId}から切断されました`
        );
        break;
      }
    }
  });

  socket.on("vote", (data) => {
    const { cardId, userId, playerId, roomId} = data;
    const room = rooms[roomId];
    if(room.cardPlayedBy.length !== room.players.length){
      socket.emit('message',{
        message: '全プレイヤーがカードを出し終えていません',
      });
      console.log('error');
      return;
    }
    
    if(!room.votedPlayers)room.votedPlayers ={};

    if(room.votedPlayers[userId]){
      socket.emit('message',{
        message:'すでに投票ずみです',
      });
      return;
    }
    room.votedPlayers[userId] = true;

    let userName;
    let playerName;
    const players= room.players;

    if(players){
      const player = players.find(player => player.userId === playerId);
      const user = players.find(player => player.userId === userId);
      if(player){
        userName = player.name;
        playerName = user.name;
      }else{
        console.log('playern not found');
      } 
    }
    
    if(!room.votes) room.votes = {};    
    if(!room.votes[cardId]) room.votes[cardId] = [];

    room.votes[cardId].push({ playerId, userName });
    const message=`${playerName}が${cardId}に投票しました`;
    io.to(roomId).emit("message",{message:message});

    io.to(roomId).emit("updateVotes",{ votes:room.votes, cardId:cardId ,player:playerId,userName:userName});
    
    console.log(room.votes);

    const totalVotes = Object.values(room.votedPlayers).length;


    if(totalVotes === room.players.length){
      console.log('全員が投票しました。結果を集計します');
      const results = room.votes;
      io.to(roomId).emit("votingResults",{results:results, roomId:roomId});
      resetturn(roomId);
    }  
  });

  function resetturn(roomId){
    const room = rooms[roomId];

    if(room){
      room.players.forEach(player => player.voted = false);
    }

    updateScores(roomId);
    room.round = (room.round || 0 ) + 1;

    const story =stories[room.round] || "冒険は終わった";
    io.to(roomId).emit('storyDisplay',{story:story});
    
    if(room.round > 3){
      endgame(roomId);
    }else{
      io.to(roomId).emit('nextRound',{
        message: "次のラウンドが始まります",
        round: room.round,
      });
    }
  }

  function updateScores(roomId) {

    const room = rooms[roomId];
    const voteCounts = {};
    for(const [cardId, votes] of Object.entries(room.votes)){
      voteCounts[cardId] = votes.length;
    }

    const winningCardId = Object.keys(voteCounts).reduce((a, b)=>
      voteCounts[a] > voteCounts[b] ? a : b,
    null
    )

    const winner= room.playedCards.find((card) => card.id === winningCardId);
    const playerId = winner ? winner.playerId : null;

    if (winningCardId) {
      // `votes` から勝者の `playerId` を取得
      const winningVote = room.votes[winningCardId][0]; // 1票目を使用
      const playerId = winningVote.playerId;

      const player = room.players.find((p) => p.userId === playerId);
      if (player) {
          player.score += 1;
          console.log('勝者', winningVote);
      } else {
          console.log('勝者のプレイヤーが見つかりません');
      }  

      io.to(roomId).emit('updatescore',room.players)

    }
  }
  
  function endgame(roomId){
    const room= rooms[roomId];

    if(!room){
      console.error(`ルーム ${roomId} が見つかりません`);
      return;
    }

    let highestScore = -1;
    let winners =[];

    room.players.forEach((player) => {
      if(player.score > highestScore){
        highestScore = player.score;
        winners = [player];
      }else if(player.score === highestScore){
        winners.push(player);
      }
    })

    io.to(roomId).emit("gameEnd",{
      message:"ゲーム終了",
      winners: winners.map((winner) => ({
        name : winner.name,
        score: winner.score,
      })),
      highestScore: highestScore,
      roomId
    });
    console.log(`ルーム${roomId} のゲームが終了しました. 勝者:`,winners);

  }
  

  socket.on('nextTurn',(data) => {
    const {roomId} = data;
    const room = rooms[roomId];
    if(!room){
      console.error('ルームが存在しません');
      return;
    }

    room.players.forEach((player,index) => {
      while(player.hands.length < 5 && room.deck.length > 0){
        const card = room.deck.pop();
        player.hands.push({ id: `${Math.random()}-${player.hands.length}`, card });
      };
      let socketId = player.socketId;
      let hand=player.hands;
      io.to(socketId).emit("dealCards", { cards: hand });
    });
  
  
  room.votes={};
  room.playedcards=[];
  room.cardPlayedBy=[];
  room.votedPlayers={};

  });

  socket.on("chat_message", (data) => {
    io.to(data.room_id).emit("chat_message", { message: data.message });
  });
  socket.on("leaveRoom", ({ roomId, userName }) => {
    io.to(roomId).emit("playerLeft", { userName });
    socket.leave(roomId);
  });
  
  function distributeCardsToRoomPlayers(roomId) {
    const room = rooms[roomId];
    if (!room || room.players.length === 0) return;

    initializeDeck((deck) => {
      if(deck.length === 0){
        console.error("カードが正しく初期化されていません");
        return;
      }
      room.deck=deck;
      console.log(deck);
      const hands = {};
      room.players.forEach((player, index) => {
        player.hands = deck.splice(0, 5).map((card, cardIndex) => {
          return { id: `${index}-${cardIndex}`, card };
        });
      });
  
      // 各プレイヤーにカードを送信
      room.players.forEach((player) => {
        const socketId = player.socketId;
        const hand = player.hands;
        console.log(socketId);
        console.log(hand);
        io.to(socketId).emit("dealCards", { cards: hand });
      });
    })
  }

  function deleteRoomFromDB(roomId) {
    const query = "DELETE FROM rooms WHERE room_id = ?";
  
    db.query(query, [roomId], (err, result) => {
      if (err) {
        console.error("ルームの削除中にエラーが発生しました:", err);
        return;
      }
      console.log(`ルームID ${roomId} が正常に削除されました。`);
    });
  }

});
// サーバーをポート8080で起動
server.listen(8080, () => {
  console.log("Server is running on port 8080");
});
