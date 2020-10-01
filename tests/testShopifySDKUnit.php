<?php

namespace QA;

use PHPUnit\Framework\TestCase;
use SWC\SDK\Shopify;

class ShopifySDKTest extends TestCase
{
    /**
     * @covers SWC\SDK\Shopify
     */
    public function testAssertTrue() {
        $shopifySDK = new Shopify();
        $this->assertTrue($shopifySDK->get_true());
    }
}
