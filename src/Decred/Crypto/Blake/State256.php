<?php namespace Decred\Crypto\Blake;

class State256 extends AbstractState
{

    public $h = [];

    private $s;

    private $t = [0, 0];

    private $buffer;

    private $nullt;

    protected $constant = [
        0x243F6A88, 0x85A308D3, 0x13198A2E, 0x03707344,
        0xA4093822, 0x299F31D0, 0x082EFA98, 0xEC4E6C89,
        0x452821E6, 0x38D01377, 0xBE5466CF, 0x34E90C6C,
        0xC0AC29B7, 0xC97C50DD, 0x3F84D5B5, 0xB5470917
    ];

    /**
     * State256 constructor.
     *
     * @param array|null $s
     */
    public function __construct(array $s = null)
    {
        $this->s = is_array($s) && count($s) === 4 ? $s : [0, 0, 0, 0];
        $this->nullt = 0;
        $this->buffer = "";
        $this->h = [
            0x6A09E667, 0xBB67AE85, 0x3C6EF372, 0xA54FF53A,
            0x510E527F, 0x9B05688C, 0x1F83D9AB, 0x5BE0CD19
        ];
    }

    /**
     * @param string $data
     */
    public function update($data)
    {
        $left = strlen($this->buffer);
        $fill = 64 - $left;

        if ($left && (strlen($data) >= $fill)) {
            $this->t[0] = $this->t[0] + 512;
            $this->t[0] = $this->t[0] & 0xFFFFFFFF;
            if ($this->t[0] == 0) {
                $this->t[1] = $this->t[1] + 1;
            }

            $this->compress($this->buffer.substr($data, 0, $fill));

            $data = substr($data, $fill);
            $this->buffer = "";
        }

        while (strlen($data) >= 64) {
            $this->t[0] = $this->t[0] + 512;
            if ($this->t[0] == 0) {
                $this->t[1] = $this->t[1] + 1;
            }

            $this->compress(substr($data, 0, 64));
            $data = substr($data, 64);
        }

        if (strlen($data) > 0) {
            $this->buffer = $this->buffer.$data;
        } else {
            $this->buffer = "";
        }
    }

    /**
     * @return string
     */
    public function finalize()
    {
        $buflen = strlen($this->buffer);
        $lo = $this->t[0] + ($buflen << 3);
        $hi = $this->t[1];
        $zo = "\x01";
        $oo = "\x81";

        if ($lo < ($buflen << 3)) {
            $hi += 1;
        }

        $hi8 = $this->u32to8($hi);
        $lo8 = $this->u32to8($lo);
        $msglen = implode("", array_map("chr", [
            $hi8[0], $hi8[1], $hi8[2], $hi8[3],
            $lo8[0], $lo8[1], $lo8[2], $lo8[3]]
        ));

        if ($buflen === 55) {

            $this->update($oo);
            $this->t[0] = $this->t[0] + 8;

        } else {
            if ($buflen < 55) {
                if (!$buflen) {
                    $this->nullt = 1;
                }

                $this->t[0] = $this->t[0] - (440 - ($buflen << 3));
                $this->update($this->padding(0, 55 - $buflen));

            } else {

                $this->t[0] = $this->t[0] - (512 - ($buflen << 3));
                $this->update($this->padding(0, 64 - $buflen));
                $this->t[0] = $this->t[0] - 440;
                $this->update($this->padding(1, 55));
                $this->nullt = 1;

            }

            $this->update($zo);
            $this->t[0] = $this->t[0] - 8;
        }

        $this->t[0] = $this->t[0] - 64;
        $this->update($msglen);

        $out = "";
        for ($i = 0; $i < 8; $i++) {
            $out = $out.$this->u32to8s($this->h[$i]);
        }

        return $out;
    }

    /**
     * @param string $data
     */
    private function compress($data)
    {
        $m = [];

        for ($i = 0; $i < 16; $i++) {
            $m1 = $data[$i * 4];
            $m2 = $data[($i * 4) + 1];
            $m3 = $data[($i * 4) + 2];
            $m4 = $data[($i * 4) + 3];
            $m[$i] = $this->u8to32([$m1, $m2, $m3, $m4]);
        }

        $v = [
            $this->h[0], $this->h[1], $this->h[2], $this->h[3],
            $this->h[4], $this->h[5], $this->h[6], $this->h[7],
            $this->s[0] ^ $this->constant[0],
            $this->s[1] ^ $this->constant[1],
            $this->s[2] ^ $this->constant[2],
            $this->s[3] ^ $this->constant[3],
            $this->constant[4],
            $this->constant[5],
            $this->constant[6],
            $this->constant[7]
        ];

        if (!$this->nullt) {
            $v[12] = $v[12] ^ $this->t[0];
            $v[13] = $v[13] ^ $this->t[0];
            $v[14] = $v[14] ^ $this->t[1];
            $v[15] = $v[15] ^ $this->t[1];
        }

        for ($i = 0; $i < 14; $i++) {
            $v = $this->g($v, 0, 4, 8, 12, 0, $m, $i);
            $v = $this->g($v, 1, 5, 9, 13, 2, $m, $i);
            $v = $this->g($v, 2, 6, 10, 14, 4, $m, $i);
            $v = $this->g($v, 3, 7, 11, 15, 6, $m, $i);

            $v = $this->g($v, 0, 5, 10, 15, 8, $m, $i);
            $v = $this->g($v, 1, 6, 11, 12, 10, $m, $i);
            $v = $this->g($v, 2, 7, 8, 13, 12, $m, $i);
            $v = $this->g($v, 3, 4, 9, 14, 14, $m, $i);
        }

        for ($i = 0; $i < 16; $i++) {
            $ix = $i % 8;
            $this->h[$ix] = $this->h[$ix] ^ $v[$i];
        }

        for ($i = 0; $i < 8; $i++) {
            $ix = $i % 4;
            $this->h[$ix] = $this->h[$ix] ^ $this->s[$ix];
        }
    }
}
