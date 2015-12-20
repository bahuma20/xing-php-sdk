<?php
namespace Bahuma\XingSDK;

use GuzzleHttp\Client;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Subscriber\Oauth\Oauth1;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;

/**
 * Class XingSDK
 * @package Bahuma\XingSDK
 */
class XingSDK {

    /**
     * @var Client
     */
    private $client;

    /**
     * Creates a new instance of the XingSDK
     * @param array $config
     */
    function __construct(array $config) {
        $token = '';
        $token_secret = '';

        $stack = HandlerStack::create();


        $oauth_middleware = new Oauth1($config);

        $stack->push($oauth_middleware);

        $this->client = new Client([
            'base_uri' => 'https://api.xing.com/v1/',
            'handler' => $stack,
            'auth' => 'oauth',
            'headers' => [
                'Accept' => 'application/json',
                'Content-Type' => 'application/json',
            ],
        ]);
    }

    /**
     * Converts a urlencoded response from the api to an associative array
     *
     * @param StreamInterface $body The body of the response
     * @return array An associative array with the keys and values from the response
     */
    private static function decodeUrlEncodedResponse(StreamInterface $body) {
        $body = $body->getContents();
        $decodedBody = array();
        $body = explode('&', $body);

        foreach($body as $key => $value) {
            $expl = explode('=', $value);
            $decodedBody[$expl[0]] = $expl[1];
        }

        return $decodedBody;
    }

    /**
     * Converts a response into an array or stdClass using the json_decode function
     *
     * @param ResponseInterface $response The response to decode
     * @return mixed An array or stdClass
     */
    public static function decodeResponse(ResponseInterface $response) {
        $body = $response->getBody();
        $body_contents_raw = $body->getContents();
        $body_contents = json_decode($body_contents_raw);

        return $body_contents;
    }

    /**
     * Obtain a request token which can be used to obtain an request token
     *
     * @param string $callback A URL on your website where the users are redirected to after accepting the permissions
     * @return array An array containing the request_token, the request_token_secret and the authorize_url
     */
    public function getRequestToken($callback_url) {
        $response = $this->client->post('request_token', [
            'query' => [
                'oauth_callback' => $callback_url,
            ]
        ]);

        $tokens = self::decodeUrlEncodedResponse($response->getBody());
        $request_token = $tokens['oauth_token'];
        $request_token_secret = $tokens['oauth_token_secret'];

        return [
            'request_token' => $request_token,
            'request_token_secret' => $request_token_secret,
            'authorize_url' => 'https://api.xing.com/v1/authorize?oauth_token='. $request_token,
        ];
    }

    /**
     * Obtain an access token which can be stored for permanent access
     *
     * @param string $oauth_verifier The the url parameter "oauth_verifier" which is present at the redirect callback after the user has acceppted the permissions
     * @return array An array containing the access_token and the access_token_secret. These should be stored if you want permanent access.
     */
    public function getAccessToken($oauth_verifier) {
        $response = $this->client->get('access_token', [
           'query' => [
                'oauth_verifier' => $oauth_verifier,
            ],
        ]);

        $tokens = self::decodeUrlEncodedResponse($response->getBody());

        $access_token = $tokens['oauth_token'];
        $access_token_secret = $tokens['oauth_token_secret'];

        return [
            'access_token' => $access_token,
            'access_token_secret' => $access_token_secret,
        ];
    }

    /**
     * Returns a guzzle HTTP client with all the tokens and authentication done and can be used to make requests to the api
     * Head over to the guzzle HTTP client doc and see which an how requests can be made.
     *
     * @return Client A guzzle HTTP client
     */
    public function getClient() {
        return $this->client;
    }
}