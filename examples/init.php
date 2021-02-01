<?php
session_start();

use myPHPnotes\YouTubeLive;

require "../vendor/autoload.php";
require "../src/YouTubeLive.php";


$client = new Google_Client;

$youtubelive = new YouTubeLive($client, $client_id, $client_secret, $redirect_uri);