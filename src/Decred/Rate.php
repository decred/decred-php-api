<?php namespace Decred;

use Decred\Rate\RateProviderInterface;

class Rate
{
    /**
     * @var RateProviderInterface
     */
    protected $provider;

    /**
     * @var float
     */
    protected $price;

    /**
     * @var string
     */
    protected $currency;

    /**
     * Rate constructor.
     *
     * @param RateProviderInterface $provider
     * @param string                $currency
     */
    public function __construct(RateProviderInterface $provider, $currency)
    {
        $this->provider = $provider;
        $this->currency = $currency;
    }

    /**
     * @return RateProviderInterface
     */
    public function getProvider()
    {
        return $this->provider;
    }

    /**
     * @return string
     */
    public function getCurrency()
    {
        return $this->currency;
    }

    /**
     * @return float
     */
    public function getPrice()
    {
        if ($this->price === null) {
            $this->price = round((float) $this->provider->getPrice($this->currency), 4);
        }

        return $this->price;
    }

    /**
     * @return float
     */
    public function getToRate()
    {
        return round(1 / $this->getPrice(), 8);
    }

    /**
     * @return float
     */
    public function getFromRate()
    {
        return round($this->getPrice(), 8);
    }

    /**
     * @param mixed $amount
     *
     * @return string
     */
    public function convertToCrypto($amount)
    {
        return round($amount / $this->getPrice(), 8);
    }

    /**
     * @param $amount
     *
     * @return string
     */
    public function convertToFiat($amount)
    {
        return round($amount * $this->getPrice(), 4);
    }
}
