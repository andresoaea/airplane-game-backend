<?php

namespace Chat;

class Message {
    private $msg;
    private $chat;
    private $conn;

    public function __construct($chat, $conn, $msg) {
        $this->msg = $msg;
        $this->chat = $chat;
        $this->conn = $conn;
    }

    public function handle() {
        $decoded = json_decode($this->msg, true);
        if(!array_key_exists('action', $decoded)) return;
        
        switch($decoded['action']) {
            case 'getMyRoom':
                $this->createRoom();
                break;
            case 'goToRoom':
                $this->goToRoom($decoded['room']);
                break;
            default:
                $this->sendToOpponent($this->msg);
               
        }
    }

   
    private function sendToOpponent($msg) {
        if(empty($this->conn->roomId)) return;
       
        // Send message to opponent
        foreach ($this->chat->rooms[$this->conn->roomId] as $playerId => $playerConnection) {  
            if($this->conn->player['id'] != $playerId) {
                $playerConnection->send($msg);
            }   
        }
    }

    private function goToRoom($id) {
        if(empty($id)) return;

        if(array_key_exists($id, $this->chat->rooms)) {
            $this->connectToExistingRoom($id);
        } else {
            $this->conn->send(json_encode([
                'action' => 'invalidRoom'
            ]));
        }

    }


    private function connectToExistingRoom($roomId) {

        $connected = $this->pushPlayerToRoom($roomId); 

        // Notify player connected
        if($connected) {
            $room = $this->chat->rooms[$roomId];
            foreach ($room as $playerId => $playerConnection) {
                $playerConnection->send(json_encode([
                    'action'   => 'enterToRoom',
                    'room'     => $roomId,
                    'opponent' => (array_keys($room)[0] == $playerId) ? $room[array_keys($room)[1]]->player : $room[array_keys($room)[0]]->player
                ]));
            }
        }

        //var_dump(array_keys($this->chat->rooms[$id]));


    }

    private function pushPlayerToRoom($roomId) {
        if(count($this->chat->rooms) >= 2) return false;

        $this->conn->roomId = $roomId;
        $this->chat->rooms[$roomId][$this->conn->player['id']] = $this->conn;    
        return true; 
    }


    private function createRoom() {
        // Generate room code and put the player in
        $roomId = $this->generateRoomId();
        $this->chat->closeAttachedRoom($this->conn);

        $this->pushPlayerToRoom($roomId);
        
        // $this->conn->roomId = $roomId;
        // $this->chat->rooms[$roomId][$this->conn->player['id']] = $this->conn;              

        // Send room id back
        $this->conn->send(json_encode([
            'action' => 'setMyRoom',
            'room'   => $roomId
            ]));
    }


    private function generateRoomId() {
        //var_dump(array_keys($this->chat->rooms));
        $digits = 4;
        $id = str_pad(rand(0, pow(10, $digits)-1), $digits, '0', STR_PAD_LEFT);

        if(array_key_exists($id, $this->chat->rooms)) {
            return $this->generateRoomId();
        }

        return '1000'; // Development room

        //return strval($id);
       
    }

}