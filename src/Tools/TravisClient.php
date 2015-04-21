<?php namespace Tools;

use Guzzle\Http\Client;
use Exception;

/**
 * Allows interaction with travis ci
 */
class TravisClient
{
    /**
     * @var bool
     */
    private $private;

    private  $ua = 'Tools-Client/1.0.0';

    private $accept = 'application/vnd.travis-ci.2+json';

    private $public_endpoint = 'https://api.travis-ci.org';

    private $private_endpoint = '';

    public function __construct($private = false)
    {
        $this->private = $private;
    }

    public function getBuildStatus($repo)
    {
        $pattern = '%s/repos/%s/builds';

        if ($this->private){
            $uri = sprintf($pattern, $this->private_endpoint, $repo);
        } else {
            $uri = sprintf($pattern, $this->public_endpoint, $repo);
        }

        $client = new Client();
        $request = $client->createRequest(
            'GET',
            $uri,
            ['Accept' => $this->accept, 'User-Agent' => $this->ua]);

        try {
            $response = $request->send();
            $data = json_decode($response->getBody(true), true);
            $last_build = array_shift($data['builds']);
            return $last_build['state'];
        } catch (Exception $ex){
            return 'unknown';
        }
    }
}