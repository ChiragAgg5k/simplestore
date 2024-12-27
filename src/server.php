<?php

require_once __DIR__.'/../vendor/autoload.php';

use Utopia\DI\Container;
use Utopia\Http\Adapter\Swoole\Server;
use Utopia\Http\Http;
use Utopia\Http\Request;
use Utopia\Http\Response;

/**
 * @throws Exception
 */
function loadProducts(): array
{
    $jsonFile = __DIR__.'/data/products.json';

    if (! file_exists($jsonFile)) {
        throw new Exception('Products data file not found');
    }

    $jsonContent = file_get_contents($jsonFile);
    if ($jsonContent === false) {
        throw new Exception('Unable to read products data file');
    }

    $data = json_decode($jsonContent, true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        throw new Exception('Invalid JSON in products data file: '.json_last_error_msg());
    }

    return $data['products'] ?? [];
}

/**
 * @throws Exception
 */
function saveProducts(array $products): void
{
    $jsonFile = __DIR__.'/data/products.json';
    $jsonContent = json_encode(['products' => $products], JSON_PRETTY_PRINT);

    if ($jsonContent === false) {
        throw new Exception('Failed to encode products data');
    }

    $result = file_put_contents($jsonFile, $jsonContent);
    if ($result === false) {
        throw new Exception('Failed to save products data');
    }
}

try {
    $products = loadProducts();

    Http::init()
        ->desc('Log request')
        ->inject('request')
        ->action(function (Request $request) {
            echo 'Server received request: '.$request->getURI()."\n";
        });

    Http::get('/')
        ->desc('Health check')
        ->inject('response')
        ->action(
            function (Response $response) {
                $response->json(
                    [
                        'status' => 'ok',
                        'code' => 200,
                        'message' => 'Server is running',
                    ]
                );
            }
        );

    Http::get('/products')
        ->desc('Get all products')
        ->inject('response')
        ->action(
            function (Response $response) use ($products) {
                $response->json($products);
            }
        );

    Http::post('/products')
        ->desc('Create a new product')
        ->inject('request')
        ->inject('response')
        ->action(
            function (Request $request, Response $response) use (&$products) {
                $newProduct = $request->getPayload('product');

                $requiredFields = ['name', 'price', 'currency'];
                foreach ($requiredFields as $field) {
                    if (! isset($newProduct[$field])) {
                        $response->json([
                            'error' => "Missing required field: $field",
                        ]);

                        return;
                    }
                }

                $maxId = 0;
                foreach ($products as $product) {
                    $maxId = max($maxId, (int) $product['id']);
                }
                $newProduct['id'] = (string) ($maxId + 1);

                $products[] = $newProduct;

                try {
                    saveProducts($products);
                    $response->json($products);
                } catch (Exception $e) {
                    $response->json([
                        'error' => 'Failed to save product: '.$e->getMessage(),
                    ]);
                }
            }
        );

} catch (Exception $e) {
    echo 'Error: '.$e->getMessage()."\n";
    exit(1);
}

echo "Server is running at http://localhost:80\n";
$http = new Http(new Server('0.0.0.0', '80', ['open_http2_protocol' => true]), new Container, 'America/New_York');
$http->start();
