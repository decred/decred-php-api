<?php namespace Decred\Tests;

use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\TestCase;
use Decred\Rate;
use Decred\Rate\CoinMarketCap;

class RateTest extends TestCase
{
    public function test_get_price()
    {
        $data = [['price_usd' => 100.84993]];
        $response = new Response(200, [], json_encode($data));

        /** @var Client $clientMock */
        $clientMock = $this->createMock(Client::class);
        $clientMock->method('request')->willReturn($response);

        $rate = CoinMarketCap::getRate();

        /** @var CoinMarketCap $provider */
        $provider = $rate->getProvider();
        $provider->setClient($clientMock);

        $this->assertInstanceOf(Rate::class, $rate);
        $this->assertEquals('USD', $rate->getCurrency());

        $amountDCR = $rate->convertToCrypto(7.09);
        $this->assertTrue(is_numeric($amountDCR));
        $this->assertEquals(0.00991572, $rate->getToRate(), '', 0.00000001);
        $this->assertEquals(100.8499, $rate->getFromRate(), '', 0.00000001);
        $this->assertEquals(0.0703025, $amountDCR, '', 0.00000001);
        $this->assertEquals(7.09, $rate->convertToFiat($amountDCR), '', 0.0001);

        $amountDCR = $rate->convertToCrypto(10.137);
        $this->assertTrue(is_numeric($amountDCR));
        $this->assertEquals(0.00991572, $rate->getToRate(), '', 0.00000001);
        $this->assertEquals(100.8499, $rate->getFromRate(), '', 0.00000001);
        $this->assertEquals(0.10051572, $amountDCR, '', 0.00000001);
        $this->assertEquals(10.137, $rate->convertToFiat($amountDCR), '', 0.0001);
    }

    public function test_no_price()
    {
        $this->expectExceptionMessage('Missing currency rate USD!');

        /** @var Client $clientMock */
        $clientMock = $this->createMock(Client::class);
        $clientMock->method('request')->willReturn(new Response(200, [], json_encode([[]])));

        $service = new CoinMarketCap();
        $service->setClient($clientMock);
        $service->getPrice();
    }

    public function test_failed_request()
    {
        $this->expectExceptionMessage('Fetching rates request failed!');

        /** @var Client $clientMock */
        $clientMock = $this->createMock(Client::class);
        $clientMock->method('request')->willReturn(new Response(400));

        $service = new CoinMarketCap();
        $service->setClient($clientMock);
        $service->getPrice();
    }

    public function test_wrong_response()
    {
        $this->expectExceptionMessage('Get wrong response on rates requests!');

        /** @var Client $clientMock */
        $clientMock = $this->createMock(Client::class);
        $clientMock->method('request')->willReturn(new Response());

        $service = new CoinMarketCap();
        $service->setClient($clientMock);
        $service->getPrice();
    }
}
