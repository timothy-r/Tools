<?php

require(__DIR__.'/vendor/autoload.php');

use Guzzle\Http\Client;

$client = new Client();

$ua = 'TimClient/1.0.0';

$accept = 'application/vnd.travis-ci.2+json';
$end_point = 'https://api.travis-ci.org';

$path = '/repos/ArtCoeur/codewords-board/builds';

$headers = ['Accept' => $accept, 'User-Agent' => $ua];

$request = $client->createRequest('GET', $end_point.$path, $headers);

$response = $request->send();

var_dump($response->json());