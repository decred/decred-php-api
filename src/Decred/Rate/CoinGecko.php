<?php namespace Decred\Rate;

use Decred\Rate;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;

class CoinGecko implements RateProviderInterface
{

    const DECRED_TICKER = 'decred';

    protected $client;

    public function __construct()
    {
        $this->client = new Client([
            'base_url' => 'https://api.coingecko.com/api/v3',
            'timeout' => 10
        ]);
    }

    /**
     * @param Client $client
     * @return self
     */
    public function setClient(Client $client): self
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
     * @return string
     * @throws GuzzleException
     */
    public function getPrice($currency = self::DEFAULT_CURRENCY)
    {
        $path = sprintf('/simple/price/?ids=%s&vs_currencies=%s', static::DECRED_TICKER, $currency);
        $response = $this->request($path);
        $key = strtolower($currency);

        if (!isset($response[static::DECRED_TICKER]) || !isset($response[static::DECRED_TICKER][$key])) {
            throw new \RuntimeException(sprintf('Failed getting price for %s', $currency));
        }

        return $response[static::DECRED_TICKER][$key];
    }

    /**
     * @param string $url
     * @return array
     * @throws GuzzleException
     */
    protected function request($url)
    {
        $response = $this->client->request('GET', $url);

        if ($response->getStatusCode() !== 200) {
            throw new \RuntimeException('Fetching rates request failed!');
        }

        return @json_decode($response->getBody(), true);
    }
}
