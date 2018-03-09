<?php namespace Decred\Crypto;

use Mdanter\Ecc\EccFactory;
use Mdanter\Ecc\Primitives\CurveFpInterface;
use Mdanter\Ecc\Primitives\GeneratorPoint;
use Mdanter\Ecc\Primitives\PointInterface;
use Mdanter\Ecc\Serializer\Point\CompressedPointSerializer;

class Curve
{
    /**
     * @var CurveFpInterface
     */
    protected static $curve;

    /**
     * @var GeneratorPoint
     */
    protected static $generator;

    /**
     * @var CompressedPointSerializer
     */
    protected static $serializer;

    /**
     * @return \Mdanter\Ecc\Primitives\CurveFpInterface
     */
    public static function curve()
    {
        if (!static::$curve) {
            static::$curve = EccFactory::getSecgCurves()->curve256k1();
        }

        return static::$curve;
    }

    /**
     * @return GeneratorPoint
     */
    public static function generator()
    {
        if (!static::$generator) {
            static::$generator = EccFactory::getSecgCurves()->generator256k1();
        }

        return static::$generator;
    }

    /**
     * @return CompressedPointSerializer
     */
    public static function serializer()
    {
        if (!static::$serializer) {
            static::$serializer = new CompressedPointSerializer(static::generator()->getAdapter());
        }

        return static::$serializer;
    }

    /**
     * @param PointInterface $point
     *
     * @return string
     */
    public static function serializePoint(PointInterface $point)
    {
        return hex2bin(static::serializer()->serialize($point));
    }

    /**
     * @param string $data
     *
     * @return PointInterface
     */
    public static function unserializePoint($data)
    {
        return static::serializer()->unserialize(static::curve(), bin2hex($data));
    }

    /**
     * @param \GMP $p
     *
     * @throws \RuntimeException
     */
    public static function ensureUsableKey($p)
    {
        if (((gmp_cmp($p, 0)) == 0) || (gmp_cmp($p, Curve::generator()->getOrder()) >= 0)) {
            throw new \RuntimeException('The extended key at this index is invalid');
        }
    }

    /**
     * Is key on the secp256k1 curve.
     *
     * @param string $key
     *
     * @return bool
     */
    public static function ensureOnCurve($key)
    {
        $point = self::unserializePoint($key);
        if (!static::curve()->contains($point->getX(), $point->getY())) {
            throw new \InvalidArgumentException(
                'Invalid key. Point is not on the curve.'
            );
        }
    }
}
