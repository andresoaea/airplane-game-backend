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

        $conn->player = [
            'id'    => $playerId,
            'name'  => 'Player ' . $playerId
        ];
        $conn->roomId = null;

       $this->clients->attach($conn);
    }

    public function onMessage(ConnectionInterface $from, $msg) {

        $message = new Message($this, $from, $msg);

        

        // foreach ($this->clients as $client) {
        //     if ($from == $client) {
        //         $from->send($msg);
        //     }
        // }
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

       
        // Notify opponent disconnected
        foreach ($this->rooms[$conn->roomId] as $playerId => $playerConnection) {
            
            if($conn->player['id'] != $playerId) {
                $playerConnection->send(json_encode([
                    'action' => 'opponentDisconnected'
                ]));
            }
            
        }
        
        // If connection is attached to a room, delete that romm
        if(!empty($conn->roomId) && array_key_exists($conn->roomId, $this->rooms)) {
            unset($this->rooms[$conn->roomId]);
        }

        //var_dump(array_keys($this->rooms));

        
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