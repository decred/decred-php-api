<?php namespace Decred\Tests\Crypto;

use Decred\TestNet;
use Decred\Tests\Mock\GeneralNetwork;
use PHPUnit\Framework\TestCase;
use Decred\Crypto\ExtendedKey;
use Decred\MainNet;

class ExtendedKeyTest extends TestCase
{

    public function test_generate_seed()
    {
        $network = GeneralNetwork::instance();
        $this->assertEquals(32, strlen(ExtendedKey::generateSeed($network)));
        $this->assertEquals(16, strlen(ExtendedKey::generateSeed($network, 16)));
        $this->assertEquals(64, strlen(ExtendedKey::generateSeed($network, 64)));
    }

    public function test_generate_seed_wrong_length()
    {
        $this->expectExceptionMessage('Invalid seed length. Length should be between 16 and 64 bytes (32 recommended).');
        ExtendedKey::generateSeed(GeneralNetwork::instance(), 2);
    }

    public function test_generate_new_master_seed_wrong_size()
    {
        $this->expectExceptionMessage('Invalid seed length. Length should be between 16 and 64 bytes (32 recommended).');
        ExtendedKey::newMaster('22', GeneralNetwork::instance());
    }

    public function test_bip32_vector1()
    {
        $seed = hex2bin('000102030405060708090a0b0c0d0e0f');
        $master = ExtendedKey::newMaster($seed, GeneralNetwork::instance());

        $this->assertEquals(
            'xpub661MyMwAqRbcFtXgS5sYJABqqG9YLmC4Q1Rdap9gSE8NqtwybGhePY2gZ29ESFjqJoCu1Rupje8YtGqsefD265TMg7usUDFdp6W1EGMcet8',
            $master->neuter()->__toString()
        );
        $this->assertEquals(
            'xprv9s21ZrQH143K3QTDL4LXw2F7HEK3wJUD2nW2nRk4stbPy6cq3jPPqjiChkVvvNKmPGJxWUtg6LnF5kejMRNNU3TGtRBeJgk33yuGBxrMPHi',
            $master->__toString()
        );

        // m/0H
        $m0H = $master->hardenedChildKey(0);
        $this->assertEquals(
            'xpub68Gmy5EdvgibQVfPdqkBBCHxA5htiqg55crXYuXoQRKfDBFA1WEjWgP6LHhwBZeNK1VTsfTFUHCdrfp1bgwQ9xv5ski8PX9rL2dZXvgGDnw',
            $m0H->neuter()->__toString()
        );
        $this->assertEquals(
            'xprv9uHRZZhk6KAJC1avXpDAp4MDc3sQKNxDiPvvkX8Br5ngLNv1TxvUxt4cV1rGL5hj6KCesnDYUhd7oWgT11eZG7XnxHrnYeSvkzY7d2bhkJ7',
            $m0H->__toString()
        );

        // m/0H/1
        $m0H_1 = $m0H->privateChildKey(1);
        $this->assertEquals(
            'xpub6ASuArnXKPbfEwhqN6e3mwBcDTgzisQN1wXN9BJcM47sSikHjJf3UFHKkNAWbWMiGj7Wf5uMash7SyYq527Hqck2AxYysAA7xmALppuCkwQ',
            $m0H_1->neuter()->__toString()
        );
        $this->assertEquals(
            'xprv9wTYmMFdV23N2TdNG573QoEsfRrWKQgWeibmLntzniatZvR9BmLnvSxqu53Kw1UmYPxLgboyZQaXwTCg8MSY3H2EU4pWcQDnRnrVA1xe8fs',
            $m0H_1->__toString()
        );

        // m/0H/1/2H
        $m0H_1_2H = $m0H_1->hardenedChildKey(2);
        $this->assertEquals(
            'xpub6D4BDPcP2GT577Vvch3R8wDkScZWzQzMMUm3PWbmWvVJrZwQY4VUNgqFJPMM3No2dFDFGTsxxpG5uJh7n7epu4trkrX7x7DogT5Uv6fcLW5',
            $m0H_1_2H->neuter()->__toString()
        );
        $this->assertEquals(
            'xprv9z4pot5VBttmtdRTWfWQmoH1taj2axGVzFqSb8C9xaxKymcFzXBDptWmT7FwuEzG3ryjH4ktypQSAewRiNMjANTtpgP4mLTj34bhnZX7UiM',
            $m0H_1_2H->__toString()
        );

        // m/0H/1/2H/2
        $m0H_1_2H_2 = $m0H_1_2H->privateChildKey(2);
        $this->assertEquals(
            'xpub6FHa3pjLCk84BayeJxFW2SP4XRrFd1JYnxeLeU8EqN3vDfZmbqBqaGJAyiLjTAwm6ZLRQUMv1ZACTj37sR62cfN7fe5JnJ7dh8zL4fiyLHV',
            $m0H_1_2H_2->neuter()->__toString()
        );
        $this->assertEquals(
            'xprvA2JDeKCSNNZky6uBCviVfJSKyQ1mDYahRjijr5idH2WwLsEd4Hsb2Tyh8RfQMuPh7f7RtyzTtdrbdqqsunu5Mm3wDvUAKRHSC34sJ7in334',
            $m0H_1_2H_2->__toString()
        );

        // m/0H/1/2H/2/1000000000
        $m0H_1_2H_2_1000000000 = $m0H_1_2H_2->privateChildKey(1000000000);
        $this->assertEquals(
            'xpub6H1LXWLaKsWFhvm6RVpEL9P4KfRZSW7abD2ttkWP3SSQvnyA8FSVqNTEcYFgJS2UaFcxupHiYkro49S8yGasTvXEYBVPamhGW6cFJodrTHy',
            $m0H_1_2H_2_1000000000->neuter()->__toString()
        );
        $this->assertEquals(
            'xprvA41z7zogVVwxVSgdKUHDy1SKmdb533PjDz7J6N6mV6uS3ze1ai8FHa8kmHScGpWmj4WggLyQjgPie1rFSruoUihUZREPSL39UNdE3BBDu76',
            $m0H_1_2H_2_1000000000->__toString()
        );
    }

    public function test_bip32_vector2()
    {
        $seed = hex2bin('fffcf9f6f3f0edeae7e4e1dedbd8d5d2cfccc9c6c3c0bdbab7b4b1aeaba8a5a29f9c999693908d8a8784817e7b7875726f6c696663605d5a5754514e4b484542');
        $master = ExtendedKey::newMaster($seed, GeneralNetwork::instance());

        $this->assertEquals(
            'xpub661MyMwAqRbcFW31YEwpkMuc5THy2PSt5bDMsktWQcFF8syAmRUapSCGu8ED9W6oDMSgv6Zz8idoc4a6mr8BDzTJY47LJhkJ8UB7WEGuduB',
            $master->neuter()->__toString()
        );
        $this->assertEquals(
            'xprv9s21ZrQH143K31xYSDQpPDxsXRTUcvj2iNHm5NUtrGiGG5e2DtALGdso3pGz6ssrdK4PFmM8NSpSBHNqPqm55Qn3LqFtT2emdEXVYsCzC2U',
            $master->__toString()
        );

        // m/0
        $m0 = $master->privateChildKey(0);
        $this->assertEquals(
            'xpub69H7F5d8KSRgmmdJg2KhpAK8SR3DjMwAdkxj3ZuxV27CprR9LgpeyGmXUbC6wb7ERfvrnKZjXoUmmDznezpbZb7ap6r1D3tgFxHmwMkQTPH',
            $m0->neuter()->__toString()
        );
        $this->assertEquals(
            'xprv9vHkqa6EV4sPZHYqZznhT2NPtPCjKuDKGY38FBWLvgaDx45zo9WQRUT3dKYnjwih2yJD9mkrocEZXo1ex8G81dwSM1fwqWpWkeS3v86pgKt',
            $m0->__toString()
        );

        // m/0/2147483647H
        $m0_2147483647H = $m0->hardenedChildKey(2147483647);
        $this->assertEquals(
            'xpub6ASAVgeehLbnwdqV6UKMHVzgqAG8Gr6riv3Fxxpj8ksbH9ebxaEyBLZ85ySDhKiLDBrQSARLq1uNRts8RuJiHjaDMBU4Zn9h8LZNnBC5y4a',
            $m0_2147483647H->neuter()->__toString()
        );
        $this->assertEquals(
            'xprv9wSp6B7kry3Vj9m1zSnLvN3xH8RdsPP1Mh7fAaR7aRLcQMKTR2vidYEeEg2mUCTAwCd6vnxVrcjfy2kRgVsFawNzmjuHc2YmYRmagcEPdU9',
            $m0_2147483647H->__toString()
        );

        // m/0/2147483647H/1
        $m0_2147483647H_1 = $m0_2147483647H->privateChildKey(1);
        $this->assertEquals(
            'xpub6DF8uhdarytz3FWdA8TvFSvvAh8dP3283MY7p2V4SeE2wyWmG5mg5EwVvmdMVCQcoNJxGoWaU9DCWh89LojfZ537wTfunKau47EL2dhHKon',
            $m0_2147483647H_1->neuter()->__toString()
        );
        $this->assertEquals(
            'xprv9zFnWC6h2cLgpmSA46vutJzBcfJ8yaJGg8cX1e5StJh45BBciYTRXSd25UEPVuesF9yog62tGAQtHjXajPPdbRCHuWS6T8XA2ECKADdw4Ef',
            $m0_2147483647H_1->__toString()
        );

        // m/0/2147483647H/1/2147483646H
        $m0_2147483647H_1_2147483646H = $m0_2147483647H_1->hardenedChildKey(2147483646);
        $this->assertEquals(
            'xpub6ERApfZwUNrhLCkDtcHTcxd75RbzS1ed54G1LkBUHQVHQKqhMkhgbmJbZRkrgZw4koxb5JaHWkY4ALHY2grBGRjaDMzQLcgJvLJuZZvRcEL',
            $m0_2147483647H_1_2147483646H->neuter()->__toString()
        );
        $this->assertEquals(
            'xprvA1RpRA33e1JQ7ifknakTFpgNXPmW2YvmhqLQYMmrj4xJXXWYpDPS3xz7iAxn8L39njGVyuoseXzU6rcxFLJ8HFsTjSyQbLYnMpCqE2VbFWc',
            $m0_2147483647H_1_2147483646H->__toString()
        );

        // m/0/2147483647H/1/2147483646H/2
        $m0_2147483647H_1_2147483646H_2 = $m0_2147483647H_1_2147483646H->privateChildKey(2);
        $this->assertEquals(
            'xpub6FnCn6nSzZAw5Tw7cgR9bi15UV96gLZhjDstkXXxvCLsUXBGXPdSnLFbdpq8p9HmGsApME5hQTZ3emM2rnY5agb9rXpVGyy3bdW6EEgAtqt',
            $m0_2147483647H_1_2147483646H_2->neuter()->__toString()
        );
        $this->assertEquals(
            'xprvA2nrNbFZABcdryreWet9Ea4LvTJcGsqrMzxHx98MMrotbir7yrKCEXw7nadnHM8Dq38EGfSh6dqA9QWTyefMLEcBYJUuekgW4BYPJcr9E7j',
            $m0_2147483647H_1_2147483646H_2->__toString()
        );
    }

    public function test_bip32_vector3()
    {
        $seed = hex2bin('4b381541583be4423346c643850da4b320e46a87ae3d2a4e6da11eba819cd4acba45d239319ac14f863b8d5ab5a0d0c64d2e8a1e7d1457df2e5a3c51c73235be');
        $master = ExtendedKey::newMaster($seed, GeneralNetwork::instance());

        $this->assertEquals(
            'xpub661MyMwAqRbcEZVB4dScxMAdx6d4nFc9nvyvH3v4gJL378CSRZiYmhRoP7mBy6gSPSCYk6SzXPTf3ND1cZAceL7SfJ1Z3GC8vBgp2epUt13',
            $master->neuter()->__toString()
        );
        $this->assertEquals(
            'xprv9s21ZrQH143K25QhxbucbDDuQ4naNntJRi4KUfWT7xo4EKsHt2QJDu7KXp1A3u7Bi1j8ph3EGsZ9Xvz9dGuVrtHHs7pXeTzjuxBrCmmhgC6',
            $master->__toString()
        );

        // m/0H
        $m0H = $master->hardenedChildKey(0);
        $this->assertEquals(
            'xpub68NZiKmJWnxxS6aaHmn81bvJeTESw724CRDs6HbuccFQN9Ku14VQrADWgqbhhTHBaohPX4CjNLf9fq9MYo6oDaPPLPxSb7gwQN3ih19Zm4Y',
            $m0H->neuter()->__toString()
        );
        $this->assertEquals(
            'xprv9uPDJpEQgRQfDcW7BkF7eTya6RPxXeJCqCJGHuCJ4GiRVLzkTXBAJMu2qaMWPrS7AANYqdq6vcBcBUdJCVVFceUvJFjaPdGZ2y9WACViL4L',
            $m0H->__toString()
        );
    }

    public function test_bip32_generate_address()
    {
        $seed = hex2bin("110131b162cfba8077facb546955d0e45dca7c93c1a27379a4cf166b882508c0");
        $master = ExtendedKey::newMaster($seed, MainNet::instance());

        $this->assertEquals(
            'dprv3hCznBesA6jBufbh3Y9f5cmEa3rmeNMnr7tzTiEVbYrXmTXgnD9ZrqUPzANojB19ntJqCydHVgcwVne72cd5TJPrr5CnmVxUaUbkA3BcByq',
            $master->__toString()
        );

        $purpose = $master->hardenedChildKey(44);
        $this->assertEquals(
            'dprv3kvp7kdbp529Dm7YB4Usb3nq4z85ac3a75qa6d3Wjhr1Yxtyt3YjbvpSbZJY5e8AVooC6VKDTpAjkPaTifxc7ZtLU78smZE7qZYQKEqCnCh',
            $purpose->__toString()
        );

        $coinType = $purpose->hardenedChildKey(20);
        $this->assertEquals(
            'dprv3nar8Jwt7u8Tfh6Q5hZrz1etQyug1p464iKJA4f3pBKWpstdDAn4gttgLW5KFn6hEYvqNH6BJMRgh6ybyYHUbFC98U63RyMhPfrmHfa3K25',
            $coinType->__toString()
        );

        $account = $coinType->hardenedChildKey(20);
        $this->assertEquals(
            'dprv3oH7V64hFGP5UeYm5eTyDtzz2QyZqEHFkh8HLfjq62T2FyVvzdEhQMhg6iijuduJDndBVscHtURUMwBPrDQ4ckQ6PXgGJBHteRv6k3Nnw9W',
            $account->__toString()
        );

        $account0Pri = $account->privateChildKey(0);
        $this->assertEquals(
            'dprv3qL8MJLw4yuPBSHWXHvgxgcC9SjEtKcLJgEpZr1k3AyGhHymTWHs5LPvEjJk3yXjt2GkufkQZ6NHurjuJ6S1aHLqx8XxxeRsXqDuEnHJQ64',
            $account0Pri->__toString()
        );

        $account0Pub = $account->publicChildKey(0);
        $this->assertEquals(
            'dpubZH8DiRuE9MyB5rBGmoz3UuQSmTHWKGCQWDs9Jkx73FZuQr1QLTdU9uuwPRbEgEnMYriY9SUr4XshamuoXZC121HVqPXBSFvE57gG9pZd2Ts',
            $account0Pub->__toString()
        );

        $account0Pub = $account0Pri->neuter();
        $this->assertEquals(
            'dpubZH8DiRuE9MyB5rBGmoz3UuQSmTHWKGCQWDs9Jkx73FZuQr1QLTdU9uuwPRbEgEnMYriY9SUr4XshamuoXZC121HVqPXBSFvE57gG9pZd2Ts',
            $account0Pub->__toString()
        );
    }

    public function test_default_wallet_layout()
    {
        $masterKey = "dprv3hCznBesA6jBushjx7y9NrfheE4ZshnaKYtsoLXefmLPzrXgEiXkdRMD6UngnmBYZzgNhdEd4K3PidxcaCiR6HC9hmpj8FcrP4Cv7zBwELA";

        // m
        $master = ExtendedKey::fromString($masterKey);

        // m/0H
        $acct0 = $master->hardenedChildKey(0);

        // m/0H/0
        $acct0Ext = $acct0->privateChildKey(0);

        // m/0H/1
        $acct0Int = $acct0->privateChildKey(1);

        // m/0H/0/10
        $acct0Ext10 = $acct0Ext->privateChildKey(10);

        // m/0H/1/0
        $acct0Int0 = $acct0Int->privateChildKey(0);

        $this->assertEquals('DshMmJ3bfvMDdk1mkXRD3x5xDuPwSxoYGfi', $acct0Ext10->getAddress());
        $this->assertEquals('DsoTyktAyEDkYpgKSex6zx5rrkFDi2gAsHr', $acct0Int0->getAddress());

        // Neuter the master key to generate a master public extended key.  This
        // gives the path:
        //   N(m/*)
        $masterNeuter = $master->neuter();

        $this->assertInstanceOf(MainNet::class, $master->getNetwork());

        $this->assertEquals(
            'dpubZ9169KDAEUnypHbWCe2Vu5TxGEcqJeNeX6XCYFU1fqw2iQZK7fsMhzsEFArbLmyUdprUw9aXHneUNd92bjc31TqC6sUduMY6PK2z4JXDS8j',
            $masterNeuter->__toString()
        );
    }

    public function test_derive_wrong_coin_type()
    {
        $this->expectExceptionMessage('Invalid coin type');

        $seed = hex2bin('000102030405060708090a0b0c0d0e0f');
        $master = ExtendedKey::newMaster($seed, $network = GeneralNetwork::instance());
        $master->deriveCoinTypeKey(ExtendedKey::MAX_COIN_TYPE + 1);
    }

    public function test_derive_hardened_key_from_public()
    {
        $this->expectExceptionMessage('Cannot derive a hardened key from a public key');

        $seed = hex2bin('000102030405060708090a0b0c0d0e0f');
        $master = ExtendedKey::newMaster($seed, $network = GeneralNetwork::instance());
        $master->neuter()->neuter()->publicChildKey(ExtendedKey::HARDENED_KEY_START + 1);
    }

    public function test_derive_private_from_public_key()
    {
        $this->expectExceptionMessage('Cannot derive a extended private key from a extended public key');

        $seed = hex2bin('000102030405060708090a0b0c0d0e0f');
        $master = ExtendedKey::newMaster($seed, $network = GeneralNetwork::instance());
        $master->neuter()->privateKey();
    }

    public function test_wrong_child_num()
    {
        $this->expectExceptionMessage('Invalid childnum for BIP32 key, must be in range [0 - (2^31)-1] inclusive');

        $seed = hex2bin('000102030405060708090a0b0c0d0e0f');
        $master = ExtendedKey::newMaster($seed, $network = GeneralNetwork::instance());
        new ExtendedKey(
            $master->privateKey(),
            $master->chainCode(),
            $master->depth(),
            $master->parentFP(),
            ExtendedKey::HARDENED_KEY_START + ExtendedKey::HARDENED_KEY_START + 20,
            $network,
            true
        );
    }

    public function test_wrong_parent_finger_print()
    {
        $this->expectExceptionMessage('Invalid fingerprint for BIP32 key, must be in range [0 - (2^31)-1] inclusive');

        $seed = hex2bin('000102030405060708090a0b0c0d0e0f');
        $master = ExtendedKey::newMaster($seed, $network = GeneralNetwork::instance());
        new ExtendedKey(
            $master->privateKey(),
            $master->chainCode(),
            $master->depth(),
            '-2000',
            $master->childNum(),
            $network,
            true
        );
    }

    public function test_wrong_depth()
    {
        $this->expectExceptionMessage('Invalid depth for BIP32 key, must be in range [0 - 255] inclusive');

        $seed = hex2bin('000102030405060708090a0b0c0d0e0f');
        $master = ExtendedKey::newMaster($seed, $network = GeneralNetwork::instance());
        new ExtendedKey(
            $master->privateKey(),
            $master->chainCode(),
            256,
            $master->parentFP(),
            $master->childNum(),
            $network,
            true
        );
    }

    public function test_wrong_chain_code()
    {
        $this->expectExceptionMessage('Chain code should be 32 bytes');

        $seed = hex2bin('000102030405060708090a0b0c0d0e0f');
        $master = ExtendedKey::newMaster($seed, $network = GeneralNetwork::instance());
        new ExtendedKey(
            $master->privateKey(),
            $master->chainCode() . 'fasdfasd',
            $master->depth(),
            $master->parentFP(),
            $master->childNum(),
            $network,
            true
        );
    }
}
