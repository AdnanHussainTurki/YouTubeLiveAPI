<?php

require "init.php";


header("location: " . $youtubelive->getAuthUrl());
die();