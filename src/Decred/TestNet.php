<?php namespace Decred;

use Decred\Crypto\DecredNetwork;
use Decred\Data\DataClient;

class TestNet extends DecredNetwork
{
    const DATA_URL = "https://testnet.dcrdata.org";

    const HD_PUBLIC_KEY_ID      = "\x04\x35\x87\xd1"; // tpub
    const HD_PRIVATE_KEY_ID     = "\x04\x35\x83\x97"; // tprv
    const PUB_KEY_HASH_ADDR_ID  = "\x0f\x21"; // Ts

    /**
     * @return TestNet
     */
    public static function instance()
    {
        return new TestNet();
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
        return new DataClient(self::DATA_URL);
    }
}
