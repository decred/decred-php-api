<?php namespace Decred\Crypto\Blake;

abstract class AbstractState
{
    protected $constant;

    protected $sigma = [
        [0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15],
        [14, 10, 4, 8, 9, 15, 13, 6, 1, 12, 0, 2, 11, 7, 5, 3],
        [11, 8, 12, 0, 5, 2, 15, 13, 10, 14, 3, 6, 7, 1, 9, 4],
        [7, 9, 3, 1, 13, 12, 11, 14, 2, 6, 5, 10, 4, 0, 15, 8],
        [9, 0, 5, 7, 2, 4, 10, 15, 14, 1, 11, 12, 6, 8, 3, 13],
        [2, 12, 6, 10, 0, 11, 8, 3, 4, 13, 7, 5, 15, 14, 1, 9],
        [12, 5, 1, 15, 14, 13, 4, 10, 0, 7, 6, 3, 9, 2, 8, 11],
        [13, 11, 7, 14, 12, 1, 3, 9, 5, 0, 15, 4, 8, 6, 2, 10],
        [6, 15, 14, 9, 11, 3, 0, 8, 12, 2, 13, 7, 1, 4, 10, 5],
        [10, 2, 8, 4, 7, 6, 1, 5, 15, 11, 9, 14, 3, 12, 13, 0]
    ];

    protected $padding = [
        0x80, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0,
        0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0,
        0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0,
        0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0,
        0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0,
        0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0,
        0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0,
        0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0
    ];

    abstract public function __construct(array $s = null);

    abstract public function update($data);

    abstract public function finalize();

    protected function padding($start, $length)
    {
        return implode("", array_map("chr", array_slice($this->padding, $start, $length)));
    }

    protected function u8to32(array $p)
    {
        return ((unpack('C', $p[0])[1] & 0xFFFFFFFF) << 24) |
            ((unpack('C', $p[1])[1] & 0xFFFFFFFF) << 16) |
            ((unpack('C', $p[2])[1] & 0xFFFFFFFF) << 8) |
            ((unpack('C', $p[3])[1] & 0xFFFFFFFF));
    }

    protected function u32to8($u32)
    {
        return [
            (($u32 >> 24) & 0xFF),
            (($u32 >> 16) & 0xFF),
            (($u32 >> 8) & 0xFF),
            (($u32) & 0xFF)
        ];
    }

    protected function u32to8s($u32)
    {
        $p = $this->u32to8($u32);

        return "".
            sprintf("%02x", $p[0]).
            sprintf("%02x", $p[1]).
            sprintf("%02x", $p[2]).
            sprintf("%02x", $p[3]);
    }

    protected function g($v, $a, $b, $c, $d, $e, $m, $round)
    {
        $i = $round % 10;
        $j = $m[$this->sigma[$i][$e]] ^ $this->constant[$this->sigma[$i][$e + 1]];
        $k = $m[$this->sigma[$i][$e + 1]] ^ $this->constant[$this->sigma[$i][$e]];
        $v[$a] = $v[$a] + $v[$b] + $j;
        $v[$a] = (int) $v[$a] & 0xffffffff;
        $v[$d] = $this->rotate($v[$d] ^ $v[$a], 16);
        $v[$d] = (int) $v[$d] & 0xffffffff;
        $v[$c] = $v[$c] + $v[$d];
        $v[$c] = (int) $v[$c] & 0xffffffff;
        $v[$b] = $this->rotate($v[$b] ^ $v[$c], 12);
        $v[$b] = (int) $v[$b] & 0xffffffff;
        $v[$a] = $v[$a] + $v[$b] + $k;
        $v[$a] = (int) $v[$a] & 0xffffffff;
        $v[$d] = $this->rotate($v[$d] ^ $v[$a], 8);
        $v[$d] = (int) $v[$d] & 0xffffffff;
        $v[$c] = $v[$c] + $v[$d];
        $v[$c] = (int) $v[$c] & 0xffffffff;
        $v[$b] = $this->rotate($v[$b] ^ $v[$c], 7);
        $v[$b] = (int) $v[$b] & 0xffffffff;
        return $v;
    }

    private function rotate($x, $n)
    {
        return (($x << (32 - $n)) & 0xFFFFFFFF) | ((($x) >> ($n) & 0xFFFFFFFF));
    }
}
