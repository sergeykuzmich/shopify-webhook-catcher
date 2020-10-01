<?php
namespace SWC;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Factory\AppFactory;

class App
{
    /**
     * Stores an instance of the Slim application.
     *
     * @var \Slim\App
     */
    private $app;

    public function __construct() {
        $app = AppFactory::create();

        $app->get('/health', function (Request $request, Response $response, $args) {
            $response->getBody()->write("OK");
            return $response;
        });

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

            $raw_headers = $request->getHeaders();
            $raw_body = $request->getBody()->getContents();

            $headers = [];
            foreach ($raw_headers as $name => $values) {
                $headers[$name] = implode(", ", $values);
            }

            $body = json_decode($raw_body);

            $result = $collection->insertOne([
              'headers' => $headers,
              'body' => $body
            ]);

            # send success response to prevent re-sending webhook
            return $response->withStatus(200);
        });

        # response 403 to any other request
        $app->any('/[{URI}]', function (Request $request, Response $response, $args) {
            return $response->withStatus(403);
        });

        $this->app = $app;
    }

    /**
     * Get an instance of the application.
     *
     * @return \Slim\App
     */
    public function get()
    {
        return $this->app;
    }
}
