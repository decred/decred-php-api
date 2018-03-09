<?php
use Decred\Crypto\Blake\State256;
include __DIR__.'/../vendor/autoload.php';

if (count($argv) < 2 || !is_string($argv[1])) {
    die(sprintf("Get BLAKE256 hash of a file.\n Usage: php %s file\n", $argv[0]));
}

// Open provided file for reading
$file = $argv[1];
$handler = fopen($file, 'r');
if ($handler === false) {
    throw new \InvalidArgumentException(sprintf('Can\'t open file `%s` for reading!', $file));
}

// Create empty state for blake 256
$blakeState = new State256();

while (!feof($handler)) {

    // Read 64 bytes of provided file
    $buffer = fread($handler, 64);

    // Append additional data to hash for blake state
    $blakeState->update($buffer);
}

// This function will append missing bytes for proper blake function
// and will return 32 characters long hash string.
$hash = $blakeState->finalize();

echo sprintf("BLAKE256: %s\n", $hash);
