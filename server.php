<?php 

require 'vendor/autoload.php';  
use Ratchet\MessageComponentInterface;  
use Ratchet\ConnectionInterface;

require 'chat.php';

// Run the server application through the WebSocket protocol on port 8080
$app = new Ratchet\App("192.168.0.105", 8080, '0.0.0.0');
$app->route('/comm', new Chat, array('*'));

$app->run();
