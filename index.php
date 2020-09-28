<?php
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Factory\AppFactory;

require __DIR__ . '/vendor/autoload.php';

$app = AppFactory::create();

$app->post('/catch', function (Request $request, Response $response, $args) {

    # validate signature
    $hmac_header = $request->getHeaderLine('X-Shopify-Hmac-Sha256');
    $data = $request->getBody()->getContents();
    $calculated_hmac = base64_encode(hash_hmac('sha256', $data, getenv('SHOPIFY_APP_SECRET'), true));
    if($hmac_header === null || $calculated_hmac === null) return $response->withStatus(403);
    if(!hash_equals($hmac_header, $calculated_hmac)) return $response->withStatus(403);

    # store request
    $client = new MongoDB\Client(getenv('DB_DSN'));
    $collection = $client->shopify->webhooks;
    $result = $collection->insertOne([
      'headers' => $request->getHeaders(),
      'parsedBody' => json_decode($request->getBody()->getContents())
    ]);

    # send success response to prevent re-sending webhook
    return $response->withStatus(200);
});

# response 403 to any other request
$app->any('/[{URI}]', function (Request $request, Response $response, $args) {
    return $response->withStatus(403);
});

$app->run();
