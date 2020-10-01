<?php

namespace QA;

use PHPUnit\Framework\TestCase;
use QA\Factory\RequestFactory;
use SWC\App;

class CatchWebhookTest extends TestCase
{
    protected $app;

    public function setUp(): void
    {
      $this->app = (new App())->get();
    }

    /**
     * @covers SWC\App
     */
    public function testHealth() {
        $request = (new RequestFactory())->createRequest('GET', '/health');
        $response = $this->app->handle($request);
        $this->assertSame($response->getStatusCode(), 200);
        $this->assertSame((string)$response->getBody(), "OK");
    }

    /**
     * @covers SWC\App
     */
    public function testWrongURI() {
        $request = (new RequestFactory())->createRequest('GET', '/');
        $response = $this->app->handle($request);
        $this->assertSame($response->getStatusCode(), 403);
    }

    /**
     * @covers SWC\App
     */
    public function testEmptyCatch() {
        $request = (new RequestFactory())->createRequest('POST', '/catch');
        $response = $this->app->handle($request);
        $this->assertSame($response->getStatusCode(), 403);
    }
}
