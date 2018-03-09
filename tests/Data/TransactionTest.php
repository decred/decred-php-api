<?php namespace Decred\tests\Data;

use Decred\Data\Transaction;
use PHPUnit\Framework\TestCase;

class TransactionTest extends TestCase
{
    public function test_transaction_fail_states()
    {
        $transaction = new Transaction(['txid' => 'test', 'time' => 'test']);
        $this->assertEquals(0, $transaction->getConfirmations());
    }

    public function test_transaction_wrong_data()
    {
        $this->expectExceptionMessage('Wrong transaction data!');
        new Transaction([]);
    }
}
