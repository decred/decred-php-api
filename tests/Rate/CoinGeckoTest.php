<?php namespace Decred\Tests\Rate;

use Decred\Rate\CoinGecko;
use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\TestCase;

class CoinGeckoTest extends TestCase
{
    public function test_get_price()
    {
        $response = new Response(200, [], json_encode([
            CoinGecko::DECRED_TICKER => [
                'usd' => 21.57
            ]
        ]));

        /** @var Client $clientMock */
        $clientMock = $this->createMock(Client::class);
        $clientMock->method('request')->withAnyParameters()->willReturn($response);

        $rate = CoinGecko::getRate();

        /** @var CoinGecko $provider */
        $provider = $rate->getProvider();
        $provider->setClient($clientMock);

        $this->assertEquals('USD', $rate->getCurrency());

        $amountDCR = $rate->convertToCrypto(8);

        $this->assertEquals(0.37088549, $amountDCR, '', 0.00000001);

        $amountUSD = $rate->convertToFiat(10);

        $this->assertEquals(215.7, $amountUSD, '', 0.00000001);
    }
}
