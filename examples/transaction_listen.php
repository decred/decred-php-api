<?php

include __DIR__.'/../vendor/autoload.php';

if (count($argv) < 2 || !is_string($argv[1])) {
    die(sprintf("Look for transactions on address.\n Usage: php %s address\n", $argv[0]));
}

$address = $argv[1];
$stop = false;

// Get network to work with
$network = \Decred\TestNet::instance();

// Get dcrdata API client
$client = $network->getDataClient();

while (!$stop) {

    $transactions = $client->getAddressRaw($address);

    echo "Transactions: \n";
    var_export($transactions);
    sleep(1);
}
