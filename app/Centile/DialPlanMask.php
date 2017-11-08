<?php

namespace Provisioning\Centile;

use Provisioning\Centile\DialPlan;
use JsonSerializable;

class DialPlanMask implements JsonSerializable
{
    protected $string;
    protected $length;
    protected $prefix;

    public function __construct($string)
    {
        $this->string = $string;
        list($this->length, $this->prefix) = explode(':', $string);
    }

    public function getString()
    {
        return $this->string;
    }

    public function getPrefix()
    {
        return $this->prefix;
    }

    public function getLength()
    {
        return $this->length;
    }

    // get all masks that include this mask
    // example: if mask is 3:412, this method will return 3:4, 3:41, 3:412
    public function getIncludingMasks()
    {
        $masks = [];
        for ($i = 1; $i <= strlen($this->prefix); $i++) {
            $masks[] = new self($this->length . ':' . substr($this->prefix, 0, $i));
        }
        return $masks;
    }

    public function inArray(array $masks)
    {
        foreach ($masks as $mask)
            if ($this->string == $mask->getString())
                return true;

        return false;
    }

    public function isRestricted()
    {
        foreach (DialPlan::getReservedMasks() as $reserved) {
            if ($reserved->getLength() == $this->length && $reserved->getPrefix() == $this->prefix)
                return true;
        }

        return false;
    }

    public function __toString()
    {
        return $this->string;
    }

    public function jsonSerialize()
    {
        return $this->toArray();
    }

    public function toArray()
    {
        $attributes = [];
        foreach (get_object_vars($this) as $name => $value)
            $attributes[$name] = $value;

        return $attributes;
    }
}
