<?php namespace Decred\Rate;

use Decred\Rate;

interface RateProviderInterface
{
    const DEFAULT_CURRENCY = 'USD';

    /**
     * @param string $currency
     *
     * @return Rate
     */
    public static function getRate($currency = self::DEFAULT_CURRENCY);

    /**
     * @param string $currency
     *
     * @return string
     */
    public function getPrice($currency = self::DEFAULT_CURRENCY);
}
