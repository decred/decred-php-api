<?php namespace Decred\Crypto;

/**
 */
class ExtendedKey
{
    /**
     * Default account index
     */
    const DEFAULT_ACCOUNT = 0;

    /**
     * BIP44 purpose index
     */
    const BIP44_PURPOSE = 44;

    /**
     * Decred coin type index
     */
    const DECRED_COIN_TYPE = 20;

    // ExternalBranch is the child number to use when performing BIP0044
    // style hierarchical deterministic key derivation for the external
    // branch.
    const EXTERNAL_BRANCH = 0;

    // InternalBranch is the child number to use when performing BIP0044
    // style hierarchical deterministic key derivation for the internal
    // branch.
    const INTERNAL_BRANCH = 1;

    /**
     * Hardened index gap
     */
    const HARDENED_KEY_START = 0x80000000;

    // maxCoinType is the maximum allowed coin type used when structuring
    // the BIP0044 multi-account hierarchy.  This value is based on the
    // limitation of the underlying hierarchical deterministic key
    // derivation.
    const MAX_COIN_TYPE = self::HARDENED_KEY_START - 1;

    // MaxAccountNum is the maximum allowed account number.  This value was
    // chosen because accounts are hardened children and therefore must
    // not exceed the hardened child range of extended keys and it provides
    // a reserved account at the top of the range for supporting imported
    // addresses.
    const MAX_ACCOUNT_NUM = self::HARDENED_KEY_START - 2; // 2^31 - 2

    // MinSeedBytes is the minimum number of bytes allowed for a seed to
    // a master node.
    const MIN_SEED_BYTES = 16; // 128 bits

    // MaxSeedBytes is the maximum number of bytes allowed for a seed to
    // a master node.
    const MAX_SEED_BYTES = 64; // 512 bits

    /**
     * Recommended seed length
     */
    const RECOMMENDED_SEED_BYTES = 32;

    /**
     * Master key
     */
    const MASTER_KEY = 'Bitcoin seed';

    /**
     * @var null|string
     */
    private $key;

    /**
     * @var null|string
     */
    private $publicKey;

    /**
     * @var string
     */
    private $chainCode;

    /**
     * @var int
     */
    private $depth = 0;

    /**
     * @var string
     */
    private $parentFP = '0000';

    /**
     * @var int
     */
    private $childNum;

    /**
     * @var NetworkInterface
     */
    private $network;

    /**
     * @var bool
     */
    private $isPrivate;

    /**
     * Generate verified usable seed.
     *
     * @param NetworkInterface  $network
     * @param int               $length
     *
     * @return mixed
     */
    public static function generateSeed(NetworkInterface $network, $length = self::RECOMMENDED_SEED_BYTES)
    {
        // Per [BIP32], the seed must be in range [16, 64].
        if (($length < static::MIN_SEED_BYTES) || ($length > static::MAX_SEED_BYTES)) {
            throw new \InvalidArgumentException(
                'Invalid seed length. Length should be between 16 and 64 bytes (32 recommended).'
            );
        }

        $seed = random_bytes($length);

        while (!static::verifySeed($seed, $network)) {
            // @codeCoverageIgnoreStart
            $seed = random_bytes($length);
            // @codeCoverageIgnoreEnd
        }

        return $seed;
    }

    /**
     * Verify that we can derive external branch 0 index address to check if seed is usable.
     *
     * @param string            $seed
     * @param NetworkInterface  $network
     *
     * @return ExtendedKey|bool Master key or false
     */
    public static function verifySeed($seed, $network)
    {
        try {
            $master = static::newMaster($seed, $network);
            $coinType = $master->deriveCoinTypeKey();
            $account0 = $coinType->deriveAccountKey();
            $account0->neuter();
            $account0->deriveInternalBranch();
            $externalBranch = $account0->deriveExternalBranch();
            $externalBranch0 = $externalBranch->privateChildKey(0);
            $externalBranch0->getAddress();
            return $master;
        // @codeCoverageIgnoreStart
        } catch (\Exception $e) {
            return false;
        }
        // @codeCoverageIgnoreEnd
    }

    /**
     * @param string            $seed
     * @param NetworkInterface  $network
     *
     * @return static
     */
    public static function newMaster($seed, NetworkInterface $network)
    {
        // Per [BIP32], the seed must be in range [16, 64].
        if ((strlen($seed) < static::MIN_SEED_BYTES) || (strlen($seed) > static::MAX_SEED_BYTES)) {
            throw new \InvalidArgumentException(
                'Invalid seed length. Length should be between 16 and 64 bytes (32 recommended).'
            );
        }

        $I = hash_hmac('sha512', $seed, static::MASTER_KEY, true);
        $IL = substr($I, 0, (strlen($I) / 2));
        $IR = substr($I, -(strlen($I) / 2));

        $secretKey = str_pad(gmp_export(gmp_import($IL)), 32, "\x0", STR_PAD_LEFT);

        Curve::ensureUsableKey(gmp_import($secretKey));

        return new ExtendedKey($secretKey, $IR, 0, "\x00\x00\x00\x00", 0, $network, true);
    }

    /**
     * Decode from base58 encoded string.
     *
     * @param string $key
     *
     * @return ExtendedKey
     */
    public static function fromString($key)
    {
        //   version (4) || depth (1) || parent fingerprint (4) ||
        //   child num (4) || chain code (32) || key data (33) || checksum (4)
        $payload = DecredNetwork::extendedKeyBase58Decode($key);
        $network = NetworkFactory::fromExtendedKeyVersion(substr($payload, 0, 4));
        $depth = intval(hexdec(substr($payload, 4, 1)));
        $parentFP = substr($payload, 5, 4);
        $childNum = intval(hexdec(substr($payload, 9, 4)));
        $chainCode = substr($payload, 13, 32);
        $key = substr($payload, 45, 33);
        $private = $payload[45] === "\x00";

        return new ExtendedKey($key, $chainCode, $depth, $parentFP, $childNum, $network, $private);
    }

    /**
     * Bip32ExtendedKey constructor.
     *
     * @param string $key
     * @param string $chainCode
     * @param int    $depth
     * @param string $parentFP
     * @param int    $childNum
     * @param NetworkInterface $network
     * @param bool   $isPrivate
     */
    public function __construct($key, $chainCode, $depth, $parentFP, $childNum, $network, $isPrivate = false)
    {
        if ($isPrivate) {
            Curve::ensureUsableKey(gmp_import($key));
        }

        if (!$isPrivate) {
            Curve::ensureOnCurve($key);
        }

        if ($depth < 0 || $depth > ((1 << 8) - 1)) {
            throw new \InvalidArgumentException(
                'Invalid depth for BIP32 key, must be in range [0 - 255] inclusive'
            );
        }

        if (gmp_import($parentFP) < 0 || gmp_import($parentFP) > ((1 << 32) - 1)) {
            throw new \InvalidArgumentException(
                'Invalid fingerprint for BIP32 key, must be in range [0 - (2^31)-1] inclusive'
            );
        }

        if ($childNum < 0 || $childNum > ((1 << 32) - 1)) {
            throw new \InvalidArgumentException(
                'Invalid childnum for BIP32 key, must be in range [0 - (2^31)-1] inclusive'
            );
        }

        if (strlen($chainCode) !== 32) {
            throw new \RuntimeException('Chain code should be 32 bytes');
        }

        $this->key = $isPrivate ? $key : null;
        $this->publicKey = $isPrivate ? null : $key;
        $this->chainCode = $chainCode;
        $this->depth = (int) $depth;
        $this->parentFP = $parentFP;
        $this->childNum = (int) $childNum;
        $this->network = $network;
        $this->isPrivate = (bool) $isPrivate;
    }

    /**
     * @return NetworkInterface
     */
    public function getNetwork()
    {
        return $this->network;
    }

    /**
     * @return bool
     */
    public function isPrivate()
    {
        return $this->isPrivate;
    }

    /**
     * @return bool
     */
    public function isPublic()
    {
        return !$this->isPrivate;
    }

    /**
     * @return string
     */
    public function privateKey()
    {
        if ($this->isPublic() || !$this->key) {
            throw new \LogicException('Cannot derive a extended private key from a extended public key');
        }

        return $this->key;
    }

    /**
     * @return string
     */
    public function publicKey()
    {
        if ($this->publicKey === null) {
            // Get public key from private key: serP(point(privateKey))
            $this->publicKey = Curve::serializePoint(
                Curve::generator()
                    ->getPrivateKeyFrom(gmp_import($this->key))
                    ->getPublicKey()
                    ->getPoint()
            );
        }

        return $this->publicKey;
    }

    /**
     * @return int
     */
    public function depth()
    {
        return $this->depth;
    }

    /**
     * @return string
     */
    public function chainCode()
    {
        return $this->chainCode;
    }

    /**
     * @return int|string
     */
    public function parentFP()
    {
        return $this->parentFP;
    }

    /**
     * @return int
     */
    public function childNum()
    {
        return $this->childNum;
    }

    /**
     * @param $index
     *
     * @return ExtendedKey
     */
    public function hardenedChildKey($index)
    {
        return $this->privateChildKey(self::HARDENED_KEY_START + $index);
    }

    /**
     * @param int|\GMP $index
     *
     * @return ExtendedKey
     */
    public function privateChildKey($index)
    {
        $key = $this->privateKey();

        if ($index >= self::HARDENED_KEY_START) {
            // Data = 0x00 || ser256(kpar) || ser32(i)).
            // The 0x00 pads the private key to make it 33 bytes long.
            $data = "\x00".str_pad(gmp_export(gmp_import($key)), 32, "\x0", STR_PAD_LEFT).pack('N', $index);
        } else {
            $data = $this->publicKey().pack('N', $index);
        }

        $I = hash_hmac('sha512', $data, $this->chainCode, true);
        $IL = substr($I, 0, (strlen($I) / 2));
        $IR = substr($I, -(strlen($I) / 2));

        Curve::ensureUsableKey(gmp_import($IL));

        $childKey = gmp_export(gmp_mod(gmp_add(gmp_import($IL), gmp_import($key)), Curve::generator()->getOrder()));

        $parentFP = substr(hex2bin(hash('ripemd160', $this->network->hashKey256($this->publicKey()))), 0, 4);

        return new ExtendedKey($childKey, $IR, $this->depth + 1, $parentFP, $index, $this->network, true);
    }

    /**
     * @param int|\GMP $index
     *
     * @return ExtendedKey
     */
    public function publicChildKey($index)
    {
        if ($this->isPublic() && $index >= self::HARDENED_KEY_START) {
            throw new \LogicException('Cannot derive a hardened key from a public key');
        }

        $key = $this->publicKey();

        $data = $key.pack('N', $index);

        $I = hash_hmac('sha512', $data, $this->chainCode, true);
        $IL = substr($I, 0, (strlen($I) / 2));
        $IR = substr($I, -(strlen($I) / 2));

        Curve::ensureUsableKey(gmp_import($IL));

        // The algorithm used to derive the public child key is:
        //   childKey = serP(point(parse256(Il)) + parentKey)
        $childKey = Curve::serializePoint(
            Curve::generator()
                ->getPrivateKeyFrom(gmp_import($IL))
                ->getPublicKey()
                ->getPoint()
                ->add(Curve::unserializePoint($key))
        );

        $parentFP = substr(hex2bin(hash('ripemd160', $this->network->hashKey256($key))), 0, 4);

        return new ExtendedKey($childKey, $IR, $this->depth + 1, $parentFP, $index, $this->network);
    }

    /**
     * Get public extended key from private or verify it is public key..
     *
     * @return ExtendedKey
     */
    public function neuter()
    {
        if ($this->isPrivate()) {
            return new ExtendedKey(
                $this->publicKey(),
                $this->chainCode,
                $this->depth,
                $this->parentFP,
                $this->childNum,
                $this->network
            );
        }

        return $this;
    }

    /**
     * @return string
     */
    public function getAddress()
    {
        return $this->network->base58EncodeAddress($this->publicKey());
    }

    /**
     * @return string
     */
    public function __toString()
    {
        $payload = $this->network->HDVersion($this->isPrivate);
        $payload .= chr($this->depth);
        $payload .= $this->parentFP;
        $payload .= pack('N', $this->childNum);
        $payload .= $this->chainCode;
        $payload .= $this->isPrivate() ? "\x00".$this->key : $this->publicKey;

        return $this->network->base58EncodeChecksum($payload);
    }

    /**
     * Derive BIP44 purpose and coin type.
     *
     * @param int $coinType
     *
     * @return ExtendedKey
     */
    public function deriveCoinTypeKey($coinType = self::DECRED_COIN_TYPE)
    {
        if ($coinType > self::MAX_COIN_TYPE) {
            throw new \InvalidArgumentException('Invalid coin type.');
        }

        return $this->hardenedChildKey(static::BIP44_PURPOSE)->hardenedChildKey($coinType);
    }

    /**
     * Derive account key after deriving purpose and coin type.
     *
     * @param int $account
     *
     * @return ExtendedKey
     */
    public function deriveAccountKey($account = self::DEFAULT_ACCOUNT)
    {
        return $this->hardenedChildKey($account);
    }

    /**
     * @return ExtendedKey
     */
    public function deriveExternalBranch()
    {
        return $this->privateChildKey(static::EXTERNAL_BRANCH);
    }

    /**
     * @return ExtendedKey
     */
    public function deriveInternalBranch()
    {
        return $this->privateChildKey(static::INTERNAL_BRANCH);
    }
}
