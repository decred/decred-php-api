<?php namespace Decred\Crypto;

use Decred\MainNet;
use Decred\TestNet;

class NetworkFactory
{
    /**
     * @param $version
     *
     * @return NetworkInterface
     */
    public static function fromExtendedKeyVersion($version)
    {
        switch ($version) {
            case MainNet::HD_PRIVATE_KEY_ID:
            case MainNet::HD_PUBLIC_KEY_ID:
                return MainNet::instance();
            case TestNet::HD_PRIVATE_KEY_ID:
            case TestNet::HD_PUBLIC_KEY_ID:
                return TestNet::instance();
            default:
                throw new \InvalidArgumentException('Unknown extended key version.');
                break;
        }
    }
}
