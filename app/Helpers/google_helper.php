<?php

use Google\Client;
use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\RequestOptions;

function getGoogleClient()
{
    $guzzleClient = new GuzzleClient([
        RequestOptions::VERIFY => 'C:/xampp/php/extras/ssl/cacert.pem'  // Pastikan path ini sesuai
    ]);

    $client = new Client();
    $client->setHttpClient($guzzleClient);
    $client->setClientId(getenv('GOOGLE_CLIENT_ID'));
    $client->setClientSecret(getenv('GOOGLE_CLIENT_SECRET'));
    $client->setRedirectUri(getenv('GOOGLE_REDIRECT_URI'));
    $client->addScope('email');
    $client->addScope('profile');

    return $client;
}
