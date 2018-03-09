<?php namespace Decred\Rate;

use GuzzleHttp\Client;
use Decred\Rate;

/**
 * Used for retrieving exchange rate from coin market cap website.
 */
class CoinMarketCap implements RateProviderInterface
{

    const DECRED_TICKER = 'decred';

    /**
     * @var Client
     */
    protected $client;

    /**
     * CoinMarketCap constructor.
     */
    public function __construct()
    {
        $this->client = new Client([
            'base_uri'  => 'https://api.coinmarketcap.com/v1/',
            'timeout'   => 10,
        ]);
    }

    /**
     * @param Client $client
     *
     * @return $this
     */
    public function setClient(Client $client)
    {
        $this->client = $client;
        return $this;
    }

    /**
     * @param string $currency
     *
     * @return Rate
     */
    public static function getRate($currency = self::DEFAULT_CURRENCY)
    {
        return new Rate(new static(), $currency);
    }

    /**
     * @param string $currency
     *
     * @return string
     */
    public function getPrice($currency = self::DEFAULT_CURRENCY)
    {
        $priceKey = 'price_'.strtolower($currency);
        $response = $this->request(sprintf('ticker/%s/?convert=%s', static::DECRED_TICKER, $currency));

        if (!isset($response[$priceKey])) {
            throw new \RuntimeException(sprintf('Missing currency rate %s!', $currency));
        }

        return $response[$priceKey];
    }

    /**
     * @param string $url
     *
     * @return array
     */
    protected function request($url)
    {
        $response = $this->client->request('GET', $url);

        if ($response->getStatusCode() !== 200) {
            throw new \RuntimeException('Fetching rates request failed!');
        }

        $json = @json_decode($response->getBody(), true);

        if (!$json || !is_array($json) || !isset($json[0])) {
            throw new \LogicException('Get wrong response on rates requests!');
        }

        return $json[0];
    }
}
