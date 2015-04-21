<?php

require(__DIR__.'/src/vendor/autoload.php');

use Tools\TravisClient;

$client = new TravisClient(false);

$status = $client->getBuildStatus('ArtCoeur/codewords-board');
var_dump($status);