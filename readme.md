# Xing PHP SDK


This is a PHP Wrapper for the Xing API based on guzzle 6.
It simplifies the process of authenticating and requesting permission.

## Installation
The best way to install Xing SDK is through composer:

The best way to install php-sdk-for-XING is through composer:

1. Download the [`composer.phar`](https://getcomposer.org/composer.phar) executable or use the installer.

    ``` sh
    $ curl -sS https://getcomposer.org/installer | php
    ```

2. add the following to your composer.json

    ``` javascript
    {
        "require": {
        	"bahuma/xing-php-sdk": "dev-master"
        }
    }
    ```

    or just run

    ``` sh
    $ composer require bahuma/xing-php-sdk
    ```

3. Run Composer: `php composer.phar install`

And you should be done.


## Example

You can find an advanced example in the file `sample.php`.

I recommend open this file and then read on.


## Obtaining an Access Token

To get an access token you first have to register your application.
Head over to [https://dev.xing.com](https://dev.xing.com) and register yourself for a Xing application
to get the consumer key/secret which you have to use with this package.

Then you have to call the following functions in this order:

1. `getRequestToken`

   Insert your `consumer_key` and your `consumer_secret` into the config array.

   Leave the `token` and `token_secret` blank.

   Then create a new XingSdk Object with this config.

   ``` php
   $config = [
     'consumer_key'    => CONSUMER_KEY,
     'consumer_secret' => CONSUMER_SECRET,
     'token'           => '',
     'token_secret'    => '',
   ];

   $xingSdk = new XingSDK($config);
   ```

   Then call the function with an url where the users are being redirected to after accepting the
   permissions. This URL is the callback-url.

   ``` php
   $result = $xing_api->getRequestToken("http://dev.bahuma.io/xing2?page=redirect");
   ```

   The function returns an array with three values.

   Save `request_token` and `request_token_secret` temporary. You'll need them in the next step.

   Redirect the user to the `authorize_url`. This is the page where the user clicks "accept".

2. `getAccessToken`

   This function should be executed at the callback-url.

   Insert your `consumer_key` and your `consumer_secret` into the config array.

   Insert the `request_token` and `request_token_secret` from the previous field into the config array.

   Then create a new XingSdk Object with this config.

      ``` php
      $config = [
        'consumer_key'    => CONSUMER_KEY,
        'consumer_secret' => CONSUMER_SECRET,
        'token'           => $the_temporary_saved_request_token,
        'token_secret'    => $the_temporary_saved_request_token_secret,
      ];

      $xingSdk = new XingSDK($config);
      ```

   Then call the function using the value of the GET-Parameter `oauth_verifier`, which has been set by
   XING.

   ``` php
   $result = $xing_api->getAccessToken($_GET['oauth_verifier']);
   ```

   The function returns an array containing the `access_token` and the `access_token_secret` for
   the user, which has logged in. Save these values in your database or somewhere else where you
   can access them later.


## Making calls to the XING-API

Now that you have obtained an access token, you can call the API. For example let's get the profile
details of the user, which has logged in.

1. Insert your `consumer_key` and your `consumer_secret` into the config array.
   Insert the `access_token` and `access_token_secret` from the user, which you have saved in your
   database,into the config array.

   ``` php
   $config = array(
     'consumer_key'    => 'abc123456789xyz',
     'consumer_secret' => 'abc123456789xyz',
     'token'           => $access_token_from_my_database,
     'token_secret'    => $access_token_secret_from_my_database,
   );
   ```

2. Create a new XingSDK Object.
   ``` php
   $xingSdk = new XingSDK($config);
   ```

3. Get the Guzzle Client from the XingSDK Object.
   ``` php
   $xingClient = $xingSDK->getClient();
   ```

4. Make the request.
   ``` php
   // "users/me" is the endpoint of the Xing-API. See https://dev.xing.com/docs/resources
   $response = $xingClient->get('users/me');
   ```

5. Bonus: Get the request in a usable format.
   ``` php
   $beautiful_response = XingSDK::decodeResponse($response);
   print_r($beautiful_response);
   ```

And that's it.

For help how to use other request methods (GET/POST/PUT/DELETE/PATCH) or send content
with your request, see the [Guzzle Documentation](http://docs.guzzlephp.org/).