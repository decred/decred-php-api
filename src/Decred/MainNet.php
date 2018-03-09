<?php namespace Decred;

use Decred\Crypto\DecredNetwork;
use Decred\Data\DataClient;

class MainNet extends DecredNetwork
{
    const DATA_URL = "https://explorer.dcrdata.org";

    const HD_PUBLIC_KEY_ID      = "\x02\xfd\xa9\x26"; // dpub
    const HD_PRIVATE_KEY_ID     = "\x02\xfd\xa4\xe8"; // dprv
    const PUB_KEY_HASH_ADDR_ID  = "\x07\x3f"; // Ds

    /**
     * @return MainNet
     */
    public static function instance()
    {
        return new MainNet();
    }

    /**
     * @param bool $isPrivate
     *
     * @return string
     */
    public function HDVersion($isPrivate)
    {
        return $isPrivate ? self::HD_PRIVATE_KEY_ID : self::HD_PUBLIC_KEY_ID;
    }

    /**
     * @return string
     */
    public function HDPubKeyHashAddrId()
    {
        return self::PUB_KEY_HASH_ADDR_ID;
    }

    /**
     * @return DataClient
     */
    public function getDataClient()
    {
        return new DataClient(static::DATA_URL);
    }
}
