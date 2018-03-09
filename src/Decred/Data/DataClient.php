<?php namespace Decred\Data;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;

class DataClient
{
    /**
     * @var Client
     */
    protected $guzzle;

    /**
     * DataClient constructor.
     *
     * @param $url
     */
    public function __construct($url)
    {
        $this->guzzle = new Client([
            'base_uri'          => $url,
            'verify'            => false,
            'allow_redirects'   => false,
        ]);
    }

    /**
     * @param Client $guzzle
     *
     * @return $this
     */
    public function setGuzzle(Client $guzzle)
    {
        $this->guzzle = $guzzle;
        return $this;
    }

    /**
     * Get address raw presentation.
     *
     * @param string         $address   Address
     * @param \DateTime|null $from      Filter older transactions
     *
     * @return array|bool|Transaction[]
     */
    public function getAddressRaw($address, \DateTime $from = null)
    {
        $result = false;

        $response = $this->request(sprintf('/api/address/%s/raw', $address));

        if ($response !== false && is_array($response)) {
            $result = [];
            foreach ($response as $data) {
                $transaction = new Transaction($data);

                if ($transaction->getOutAmount($address) !== false) {
                    if ($from === null || $transaction->getTime() > $from) {
                        $result[] = $transaction;
                    }
                }
            }
        }

        return $result;
    }

    /**
     * @param string$path
     *
     * @return array|false
     */
    protected function request($path)
    {
        try {
            return @json_decode($this->guzzle->get($path)->getBody(), true);
        } catch (\Exception $e) {
            return false;
        }
    }
}
