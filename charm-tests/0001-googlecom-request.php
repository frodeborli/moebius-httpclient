<?php
require(__DIR__.'/../vendor/autoload.php');

use Charm\Http\Message\{Request, Uri, Stream};

$request = new Request("GET", 'http://companycast.live/test/frode?a=b&c=d', '');
$client = new Moebius\Http\Client();
$response = $client->sendRequest($request);
var_dump($response);
