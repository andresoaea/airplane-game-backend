<?php
use Ratchet\MessageComponentInterface;
use Ratchet\ConnectionInterface;

class Chat implements MessageComponentInterface {
    protected $clients;
    protected $players;


    public function __construct() {
        $this->clients = new \SplObjectStorage;
        $this->players = [];
    }

    public function onOpen(ConnectionInterface $conn) {
        // Store the new connection to send messages to later
        
        
        $reqParams = $this->getRequestParams($conn);
        

        $playerId = $reqParams['playerId'];

       
        $conn->playerId = $playerId;
        $conn->opponentId = $reqParams['opponentId'];
        
       // var_dump(array_keys($this->players));

        $this->players[$playerId] = $conn;
        $this->clients->attach($conn);


        if(array_key_exists($reqParams['opponentId'], $this->players)) {
            // Notify opponent connected
            $to = $this->getOpponentConnection($conn);
            $to->send(json_encode([
                'opponentConnected' => true
            ]));

        }

        //var_dump(count($this->players));

        echo "New connection! ({$conn->resourceId}, {$playerId})\n";
    }

    public function onMessage(ConnectionInterface $from, $msg) {


        if(!$this->getOpponentConnection($from)) return;

        $to = $this->getOpponentConnection($from);
        $to->send($msg);




        // $numRecv = count($this->clients) - 1;
        // echo sprintf('Connection %d sending message "%s" to %d other connection%s' . "\n"
        //     , $from->resourceId, $msg, $numRecv, $numRecv == 1 ? '' : 's');

        // foreach ($this->clients as $client) {
        //     if ($from !== $client) {

        //         // The sender is not the receiver, send to each client connected
        //         $client->send($msg);
        //         break;
        //     }
        // }
          

    }

    public function onClose(ConnectionInterface $conn) {
        // The connection is closed, remove it, as we can no longer send it messages
        unset($this->players[$conn->playerId]);
        $this->clients->detach($conn);
        //var_dump(count($this->players));
        

        echo "Connection {$conn->resourceId} has disconnected\n";
        $this->notifyOpponentDisconnected($conn);
    }


    private function notifyOpponentDisconnected($conn) {
        if(!$this->getOpponentConnection($conn)) return;

        $to = $this->getOpponentConnection($conn);
        $to->send(json_encode([
            'action' => 'opponentDisconnected'
        ]));
    }

    public function onError(ConnectionInterface $conn, \Exception $e) {
        echo "An error has occurred: {$e->getMessage()}\n";

        $conn->close();
    }

    // Helpers methods
    private function getRequestParams($conn) {
        $reqParams = [];
        $querystring = $conn->httpRequest->getUri()->getQuery();
        parse_str($querystring, $reqParams);

        return $reqParams;
    }

    private function getOpponentConnection($conn) {
        $reqParams = $this->getRequestParams($conn);
        if(array_key_exists($reqParams['opponentId'], $this->players)) {
            return $this->players[$reqParams['opponentId']];
        }
        return false;
    }

}