<?php
namespace Chat;

use Ratchet\ConnectionInterface;
use Ratchet\MessageComponentInterface;

class Chat implements MessageComponentInterface {
    public $rooms;
    private $clients;
            
    public function __construct() {
        $this->rooms = [];
        $this->clients = new \SplObjectStorage;
    }

    public function onOpen(ConnectionInterface $conn) {
      
        $conn->roomId = null;
        $queryParams = $this->getRequestParams($conn);
        $conn->player = [
            'id'    => $queryParams['id'],
            'name'  => $queryParams['name'],
            'photo' => $queryParams['photo']
        ];

       $this->clients->attach($conn);
    }

    public function onMessage(ConnectionInterface $from, $msg) {

        $message = new Message($this, $from, $msg);
        $message->handle();

    }

    public function onClose(ConnectionInterface $conn) {
        $this->closeAttachedRoom($conn);
        $this->clients->detach($conn);
    }

    public function onError(ConnectionInterface $conn, \Exception $e) {
        $conn->close();
    }



    /**
     *  Private custom methods
     */

    // Delete dissconected player from room & rooms ids list
    public function closeAttachedRoom($conn) {
        if(empty($conn->roomId)) return;
       
        // Notify opponent disconnected
        foreach ($this->rooms[$conn->roomId] as $playerId => $playerConnection) {  
            if($conn->player['id'] != $playerId) {
                $playerConnection->send(json_encode([
                    'action' => 'opponentDisconnected'
                ]));
            }   
        }
        
        // If connection is attached to a room, delete that romm
        if(array_key_exists($conn->roomId, $this->rooms)) {
            unset($this->rooms[$conn->roomId]);
        }

    }


   

    /**
     *  Helpers -  custom methods
     */
    private function getRequestParams($conn) {
        $reqParams = [];
        $querystring = $conn->httpRequest->getUri()->getQuery();
        parse_str($querystring, $reqParams);

        return $reqParams;
    }

   
}