# Blake 256 PHP Native implementation

Implementation of BLAKE256 hash algorithm in native PHP.

## Usage

```php
$blakeState = new \Decred\Crypto\Blake\Blake256();

$handler = fopen($file, 'r');

while (!feof($handler)) {

    $buffer = fread($handler, 64);

    $blakeState->update($buffer);
}

$hash = $blakeState->finalize();

```
