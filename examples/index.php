<?php

require "init.php";


// Resetting Access Token
$youtubelive->bindToken();

// Inserting a broadcast
$broadcast = $youtubelive->createBroadcast("Test broadcast: " . mt_rand(1,300), "2019-09-19T22:59:04+0000", "private");

var_dump($broadcast);


// Inserting a stream
$stream = $youtubelive->createStream("Test stream: " . mt_rand(1,300));
var_dump($stream);

// Binding Stream to Broadcast
$bindedBroadcast = $youtubelive->bindBroadcastToStream($broadcast, $stream);
var_dump($bindedBroadcast);

