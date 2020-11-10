<?php 

require 'vendor/autoload.php';  
use Ratchet\MessageComponentInterface;  
use Ratchet\ConnectionInterface;
use Chat\Chat;

// Run the server application through the WebSocket protocol on port 8080
//$domain = "192.168.0.105";
$domain = 'localhost';
//set an array of origins allowed to connect to this server
$allowed_origins = ['localhost', '127.0.0.1', $domain];

$app = new Ratchet\App($domain, 8080, '0.0.0.0');
$app->route('/comm', new Chat, $allowed_origins);

$app->run();
