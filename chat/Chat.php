<?php
namespace Chat;

use Ratchet\MessageComponentInterface;
use Ratchet\ConnectionInterface;

class Chat implements MessageComponentInterface {
    private $clients;
    private $roomsIds;
    public $rooms;
            
    public function __construct() {
        $this->clients = new \SplObjectStorage;
        //$this->roomsIds = [];
        $this->rooms = [];

    }

    public function onOpen(ConnectionInterface $conn) {
      

        $playerId = $this->getRequestParams($conn)['playerId'];

        //$roomId = $this->generateRoomId();
        // $this->roomsIds[] = $roomId;
        // $conn->roomId = $roomId;
        $conn->playerId = $playerId;
        $conn->roomId = null;
        //$this->roomsIds[$roomId] = ['author' => $playerId];
        
       // var_dump($this->roomsIds);

       $this->clients->attach($conn);
    }

    public function onMessage(ConnectionInterface $from, $msg) {

        $send = new Message($this, $from, $msg);

        

        // foreach ($this->clients as $client) {
        //     if ($from == $client) {
        //         $from->send($msg);
        //     }
        // }
    }

    public function onClose(ConnectionInterface $conn) {
        $this->clients->detach($conn);
        //$this->closeRoom($conn);
    }

    public function onError(ConnectionInterface $conn, \Exception $e) {
        $conn->close();
    }



    /**
     *  Private custom methods
     */

    // Delete dissconected player from room & rooms ids list
    public function closeRoom($conn) {
       
        if (($key = array_search($conn->roomId, array_keys($this->rooms))) !== false) {
            unset($this->rooms[$key]);
        }

        
    }


    // private function requestRoom($conn) {
    //     return $conn->roomId;
    // }

    /**
     *  Helpers -  custom methods
     */
    private function getRequestParams($conn) {
        $reqParams = [];
        $querystring = $conn->httpRequest->getUri()->getQuery();
        parse_str($querystring, $reqParams);

        return $reqParams;
    }

    // private function generateRoomId() {
    //     $roomCode = '';
    //     $arr = $this->roomsIds;
    //     $roomsStringLen = strlen(count($arr));
        
    //     for($i=0; $i < 4; $i++) {
    //         if($i < (4 - $roomsStringLen)) {
    //             $roomCode .= '0';
    //         }else {
    //             $roomCode .= count($arr) + 1;
    //             break;
    //         }
    //     }
    //     return $roomCode;
    // }
}