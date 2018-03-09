<?php namespace Decred\Data;

class Transaction
{
    /**
     * @var array
     */
    protected $data;

    /**
     * Transaction constructor.
     *
     * @param array $data
     */
    public function __construct(array $data)
    {
        if (!isset($data['txid']) || !isset($data['time'])) {
            throw new \RuntimeException('Wrong transaction data!');
        }

        $this->data = $data;
    }

    /**
     * @param string $forAddress
     *
     * @return float|bool Return amount or FALSE on failure.
     */
    public function getOutAmount($forAddress)
    {
        if (isset($this->data['vout']) && is_array($this->data['vout'])) {
            foreach ($this->data['vout'] as $vout) {

                if (isset($vout['scriptPubKey']['addresses']) && is_array($vout['scriptPubKey']['addresses'])) {
                    foreach ($vout['scriptPubKey']['addresses'] as $address) {

                        if ($forAddress === $address && isset($vout['value'])) {
                            return (float) $vout['value'];
                        }
                    }
                }

            }
        }

        return false;
    }

    /**
     * @return false|string
     */
    public function getTxid()
    {
        return $this->data['txid'];
    }

    /**
     * @return \DateTime
     */
    public function getTime()
    {
        $time = new \DateTime();
        $time->setTimestamp($this->data['time']);
        return $time;
    }

    /**
     * @return int
     */
    public function getConfirmations()
    {
        if (isset($this->data['confirmations'])) {
            return intval($this->data['confirmations']);
        }

        return 0;
    }
}
