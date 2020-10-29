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
                // Generate room code and put the player in
                $roomId = $this->generateRoomId();
                //var_dump($roomId);
                
                $this->chat->rooms[$roomId][] = $this->conn;
                $this->conn->roomId = $roomId;


               var_dump($this->conn->roomId);
                

                    
                


              

                // Send room id back
                $this->conn->send(json_encode([
                    'action' => 'setMyRoom',
                    'room'   => $roomId
                    ]));
                break;
            case 'goToRoom':
                //
                break;
        }
    }


    private function generateRoomId() {
        //var_dump(array_keys($this->chat->rooms));
        $digits = 4;
        $id = str_pad(rand(0, pow(10, $digits)-1), $digits, '0', STR_PAD_LEFT);

        if(array_key_exists($id, $this->chat->rooms)) {
            return $this->generateRoomId();
        }

        return $id;
       
    }

}