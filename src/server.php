<?php

require_once __DIR__.'/../vendor/autoload.php';

use Utopia\Cache\Cache;
use Utopia\Cache\Adapter\Memory;
use Utopia\DI\Container;
use Utopia\Http\Adapter\Swoole\Server;
use Utopia\Http\Http;
use Utopia\Http\Request;
use Utopia\Http\Response;
use Utopia\Http\Validator\Text;

$items = [
    'Item 1',
    'Item 2',
    'Item 3',
];

try {
    Http::get('/items')
        ->inject('response')
        ->action(
            function (Response $response) use ($items) {
                $response->json($items);
            }
        );
} catch (\Utopia\Servers\Exception $e) {
    echo $e->getMessage();
}

try {
    Http::init()
        ->inject('request')
        ->action(function (Request $request) {
            echo "Server received request: ".$request->getURI()."\n";
        });
} catch (\Utopia\Servers\Exception $e) {
    echo $e->getMessage();
}

// Health check
try {
    Http::get('/')
        ->param('name', 'World', new Text(256), 'Name to greet. Optional, max length 256.', true)
        ->inject('response')
        ->action(
            function (string $name, Response $response) {
                $response->json(['message' => 'Hello '.$name]);
            }
        );
} catch (\Utopia\Servers\Exception $e) {
    echo $e->getMessage();
}

echo "Server is running. Press CTRL+C to exit.\n";
$http = new Http(new Server('0.0.0.0', '80' , ['open_http2_protocol' => true]), new Container(), 'America/New_York');
$http->start();
