<?php

require "init.php";

$tokens = $youtubelive->getTokens($_GET['code'], $_GET['state']);
$youtubelive->setTokens($tokens);

header("location: index.php");
die();