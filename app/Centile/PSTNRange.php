<?php

namespace Provisioning\Centile;

use DB;
use Provisioning\Exceptions\InvalidPSTNRangeException;

class PSTNRange
{
    const DEFAULT_PREFIX = 'COLT';

    public $rangeStart;
    public $countryCode;
    public $rangeEnd;
    public $isRegisteredInEnum = false;
    public $label;

    public function __construct($params)
    {
        foreach ($params as $param => $value) {
            if ($value === "@NULL") {
                $this->$param = null;
            }
            elseif ($value === "false")
                $this->$param = false;
            elseif ($value === "true")
                $this->$param = true;
            else
                $this->$param = $value;
        }

        if ($this->rangeStart > $this->rangeEnd)
            throw new InvalidPSTNRangeException('PSTN Range start ' . $this->rangeStart . ' is higher than PSTN range end ' . $this->rangeEnd);
    }

    public function __get($property)
    {
        return $this->$property;
    }

    public function getAllPSTNs()
    {
        $pstns = [];

        for ($cur = substr($this->rangeStart, 1); $cur <= substr($this->rangeEnd, 1); $cur++)
            $pstns[] = '+' . $cur;

        return $pstns;
    }

    public static function createRanges($pstns)
    {
        $ranges = [];

        sort($pstns);
        for ($i = 0; $i < count($pstns); ++$i) {
            $start = $pstns[$i];
            for ($j = $i; $j < count($pstns); ++$j) {
                if ($j + 1 >= count($pstns)) {
                    $ranges[] = ['start' => $start, 'end' => $pstns[$j]];
                    return $ranges;
                }
                $delta = $pstns[$j + 1] - $pstns[$j];
                if ($delta !== 1) {
                    $ranges[] = ['start' => $start, 'end' => $pstns[$j]];
                    $i = $j;
                    break;
                }
            }
        }
        return $ranges;
    }
}
