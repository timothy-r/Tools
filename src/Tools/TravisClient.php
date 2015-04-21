<?php namespace Tools;

use Guzzle\Http\Client;
use Exception;
use Guzzle\Http\Message\Request;

/**
 * Allows interaction with travis ci
 */
class TravisClient
{
    /**
     * Whether or not to use the private repo endpoint
     *
     * @var bool
     */
    private $private;

    private $ua = 'Tools-Client/1.0.0';

    private $accept = 'application/vnd.travis-ci.2+json';

    private $public_host = 'api.travis-ci.org';

    private $private_host = 'api.travis-ci.com';

    private $github_token;

    private $travis_token = null;

    /**
     * @param bool $private
     */
    public function __construct($token, $private = false)
    {
        $this->github_token = $token;
        $this->private = $private;
    }

    /**
     * Get the status of the last travis build for this repo
     *
     * @param $repo
     * @return string
     */
    public function getBuildStatus($repo)
    {
        $pattern = 'https://%s/repos/%s/builds';

        if ($this->private){
            $uri = sprintf($pattern, $this->private_host, $repo);
        } else {
            $uri = sprintf($pattern, $this->public_host, $repo);
        }

        $response = $this->makeRequest("GET", $uri);

        if (!isset($response['error'])) {
            $last_build = array_shift($response['builds']);
            return ['state' => $last_build['state'], 'finished_at' => $last_build['finished_at']];
        } else {
            return ['state' => 'unknown', 'finished_at' => 'unknown', 'error' => $response['error']];
        }
    }

    private function getTravisToken()
    {
        if (!$this->travis_token){
            $response = $this->makeRequest(
                'POST',
                sprintf("https://%s/auth/github", $this->public_host),
                ['github_token' => $this->github_token],
                false
            );

            if (!$response) {
                throw new Exception("Can't get travis token");
            }

            return $response['access_token'];
        }
        return $this->travis_token;
    }

    /**
     * @param $method
     * @param $uri
     * @param $body
     * @param bool $authn
     *
     * @return mixed|string
     */
    private function makeRequest($method, $uri, $body = null, $authn = true)
    {
        $headers = ['Accept' => $this->accept, 'User-Agent' => $this->ua];

        if ($this->private && $authn){
            // add an authn header
            $token = $this->getTravisToken();
            $headers['Authorization'] = "token $token";
        }

        if (is_array($body)){
            $body = json_encode($body);
            $headers['Content-Type'] = 'application/json';
        }

        try {
            $client = new Client();
            $request = $client->createRequest($method, $uri, $headers, $body);
            $response = $request->send();
            return json_decode($response->getBody(true), true);
        } catch (Exception $ex){
            return ['error' => $ex->getMessage()];
        }
    }
}