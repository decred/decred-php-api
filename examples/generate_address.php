<?php
include __DIR__.'/../vendor/autoload.php';

$testnet = \Decred\TestNet::instance();

// Generate seed for test net
$seed = \Decred\Crypto\ExtendedKey::generateSeed($testnet);

echo sprintf("Seed hex: %s\n", bin2hex($seed));

// Generate HD master key
$master = \Decred\Crypto\ExtendedKey::newMaster($seed, $testnet);

echo sprintf("Master HD key: %s\n\n", $master);

// Default account HD private key
$defaultAccountPrivateKey = $master
    ->hardenedChildKey(44)
    ->hardenedChildKey(42)
    ->hardenedChildKey(0);

// Default account HD public key
$defaultAccountPublicKey = $master->neuter();

echo sprintf("Default account HD private key: %s\n", $defaultAccountPrivateKey);
echo sprintf("Default account HD public key: %s\n", $defaultAccountPublicKey);

// Default address
$defaultAddress = $defaultAccountPublicKey
    ->publicChildKey(0)
    ->publicChildKey(0)
    ->getAddress();

echo sprintf("Default address: %s\n", $defaultAddress);
