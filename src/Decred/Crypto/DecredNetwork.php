<?php namespace Decred\Crypto;

abstract class DecredNetwork implements NetworkInterface
{
    static $base58;

    const ALPHABET = '123456789ABCDEFGHJKLMNPQRSTUVWXYZabcdefghijkmnopqrstuvwxyz';

    /**
     * @return \StephenHill\Base58
     */
    public static function base58()
    {
        if (!static::$base58) {
            static::$base58 = new \StephenHill\Base58(static::ALPHABET);
        }

        return static::$base58;
    }

    /**
     * Decode encrypted key and verify the checksum.
     *
     * @param string $key
     *
     * @return string
     */
    public static function extendedKeyBase58Decode($key)
    {
        // The base58-decoded extended key must consist of a serialized payload
        // plus an additional 4 bytes for the checksum.
        $decoded = static::base58()->decode($key);

        if (strlen($decoded) !== 82) {
            throw new \InvalidArgumentException('Wrong key length.');
        }

        // Split the payload and checksum up and ensure the checksum matches.
        $payload = substr($decoded, 0, 78);
        $expected = substr($decoded, -4);

        // Verify checksum of provided key
        $network = NetworkFactory::fromExtendedKeyVersion(substr($payload, 0, 4));
        $checksum = substr($network->base58Checksum($payload), 0, 4);

        if ($expected !== $checksum) {
            throw new \InvalidArgumentException('Wrong checksum on encoding extended key!');
        }

        return $decoded;
    }

    /**
     * @inheritdoc
     */
    public function base58EncodeAddress($key)
    {
        $prefix = $this->HDPubKeyHashAddrId();
        $payload = $prefix.hash('ripemd160', $this->hashKey256($key), true);
        return $this->base58()->encode($payload.$this->base58Checksum($payload));
    }

    /**
     * @inheritdoc
     */
    public function hashKey256($key)
    {
        return hex2bin(Hash::blake($key));
    }

    /**
     * @inheritdoc
     */
    public function base58EncodeChecksum($payload)
    {
        return $this->base58()->encode($payload.$this->base58Checksum($payload));
    }

    /**
     * @inheritdoc
     */
    public function base58Checksum($payload)
    {
        return substr($this->hashKey256($this->hashKey256($payload)), 0, 4);
    }
}
