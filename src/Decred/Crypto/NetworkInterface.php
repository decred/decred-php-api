<?php namespace Decred\Crypto;

use Decred\Data\DataClient;

interface NetworkInterface
{
    /**
     * Create network instance.
     *
     * @return NetworkInterface
     */
    public static function instance();

    /**
     * Apply network defined hash on key.
     *
     * @param string $key
     *
     * @return string
     */
    public function hashKey256($key);

    /**
     * @param bool $isPrivate
     *
     * @return string
     */
    public function HDVersion($isPrivate);

    /**
     * @return string
     */
    public function HDPubKeyHashAddrId();

    /**
     * @return DataClient
     */
    public function getDataClient();

    /**
     * Get base58 encoded extended key address.
     *
     * @param string $key
     *
     * @return string
     */
    public function base58EncodeAddress($key);

    /**
     * Get base58 encoded payload + 4 bytes checksum of payload.
     *
     * @param string $payload
     *
     * @return string 
     */
    public function base58EncodeChecksum($payload);

    /**
     * Get base58 4 bytes checksum of payload.
     *
     * @param string $payload
     *
     * @return string
     */
    public function base58Checksum($payload);
}
