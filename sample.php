<?php

require './vendor/autoload.php';

use Bahuma\XingSDK\XingSDK;

session_start();

// The token you get at dev.xing.com
const CONSUMER_KEY = '506c1333b2edc3146945';
const CONSUMER_SECRET = '00a74956afa278e141e0e5070a04574c9020b858';

if (!array_key_exists('tokens', $_SESSION)) {
    $_SESSION['tokens'] = [
        'request_token'        => '',
        'request_token_secret' => '',
        'access_token'         => '',
        'access_token_secret'  => '',
    ];
}

// The access should come from your database)

$default_config = [
    'consumer_key'    => CONSUMER_KEY,
    'consumer_secret' => CONSUMER_SECRET,
    'token'           => $_SESSION['tokens']['access_token'],
    'token_secret'    => $_SESSION['tokens']['access_token_secret'],
];

$default_xing_sdk = new XingSDK($default_config);

$default_xing_client = $default_xing_sdk->getClient();

function print_help()
{
    echo 'Available Pages:<br>';
    echo '<ul>';
    echo '<li><a href="?page=connect">connect</a></li>';
    echo '<li><a href="?page=redirect">redirect</a></li>';
    echo '<li><a href="?page=getUserDetails">getUserDetails</a></li>';
    echo '<li><a href="?page=updateLanguageSkill">updateLanguageSkill</a></li>';
    echo '</ul>';
}

if (!array_key_exists('page', $_GET)) {
    print_help();
    die();
}

switch ($_GET['page']) {
    case 'connect':
        // leave the token empty
        $config = [
            'consumer_key'    => CONSUMER_KEY,
            'consumer_secret' => CONSUMER_SECRET,
            'token'           => '',
            'token_secret'    => '',
        ];

        $xing_api = new XingSDK($config);
        $result = $xing_api->getRequestToken('http://dev.bahuma.io/xing2?page=redirect');

        $_SESSION['tokens']['request_token'] = $result['request_token'];
        $_SESSION['tokens']['request_token_secret'] = $result['request_token_secret'];

        echo '<a href="'.$result['authorize_url'].'">Login</a>';

        break;

    case 'redirect':
        // Use the request token, which the getRequestToken Method returned.
        $config = [
            'consumer_key'    => CONSUMER_KEY,
            'consumer_secret' => CONSUMER_SECRET,
            'token'           => $_SESSION['tokens']['request_token'],
            'token_secret'    => $_SESSION['tokens']['request_token_secret'],
        ];

        $xing_api = new XingSDK($config);
        $token = $xing_api->getAccessToken($_GET['oauth_verifier']);

        $_SESSION['tokens']['access_token'] = $token['access_token'];
        $_SESSION['tokens']['access_token_secret'] = $token['access_token_secret'];

        break;

    case 'getUserDetails':
        $response = $default_xing_client->get('users/me');

        echo '<pre>';
        print_r(XingSDK::decodeResponse($response));

        break;

    case 'updateLanguageSkill':
        $response = $default_xing_client->put('users/me/languages/de', [
            'json' => [
                'skill' => 'NATIVE',
            ],
        ]);

        echo '<pre>';
        print_r(XingSDK::decodeResponse($response));

        break;

    default:
        print_help();
}
