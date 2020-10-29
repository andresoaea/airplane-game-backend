<?php

namespace Chat;


class Message {
    private $chat;
    private $conn;
    private $msg;

    public function __construct($chat, $conn, $msg) {
        $this->chat = $chat;
        $this->conn = $conn;
        $this->msg = $msg;
        $this->handleMsg();
    }

    private function handleMsg() {
        $decoded = json_decode($this->msg, true);
        if(!array_key_exists('action', $decoded)) return;
        
        switch($decoded['action']) {
            case 'getMyRoom':
                $this->createRoom();
                break;
            case 'goToRoom':
                $this->goToRoom($decoded['room']);
                break;
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

        $this->pushPlayerToRoom($roomId); 

        // Notify player connected
        foreach ($this->chat->rooms[$roomId] as $playerConnection) {
            $playerConnection->send(json_encode([
                'action' => 'enterToRoom',
                'room'   => $roomId
            ]));
        }

        //var_dump(array_keys($this->chat->rooms[$id]));


    }

    private function pushPlayerToRoom($roomId) {
        $this->conn->roomId = $roomId;
        $this->chat->rooms[$roomId][$this->conn->player['id']] = $this->conn;     
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

        return strval($id);
       
    }

}