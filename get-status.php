<?php

require(__DIR__.'/src/vendor/autoload.php');

function usage($msg)
{
    print "get-status.php report on the status of a repo's build on travis\n";
    print "usage: get-status.php repo-name...\n";
    print "\n$msg\n";
    exit;
}

if (count($argv) < 2){
    usage('Error: needs repo arg');
}

$config = json_decode(file_get_contents(__DIR__ . '/config.json'), true);

var_dump($config);

use Tools\TravisClient;

$client = new TravisClient($config['github_token'], $private = false);

$app = array_shift($argv);

// get the status of all repos on the command line
while ($repo = array_shift($argv)) {
    $status = $client->getBuildStatus($repo);
    if (isset($status['error'])){
        printf("%s error %s\n", $repo, $status['error']);
    } else {
        printf("%s %s at %s\n", $repo, $status['state'], $status['finished_at']);
    }
}