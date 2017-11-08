<?php

namespace Provisioning\Centile;

use Provisioning\Centile\DialPlanMask;

class DialPlan
{
    protected $dialPlan;

    public function __construct($dialPlan)
    {
        if (!preg_match('/^.*;$/', $dialPlan))
            $dialPlan = $dialPlan . ';';
        $this->dialPlan = $dialPlan;
    }

    /**
     * @param  string  $dialPlan               [format: (length:prefix[,prefix]*;)+]
     * @param  boolean $allowRestrictedMasks [if true, will not check that a mask is restricted]
     * @return boolean
     */
    public static function isValid($dialPlan, $allowRestrictedMasks = true)
    {
        if (!self::hasGoodSyntax($dialPlan))
            return false;

        $maskGroups = explode(';', $dialPlan);

        // remove last item of the array because it's empty
        array_pop($maskGroups);

        $prefixes = [];
        foreach ($maskGroups as $maskGroup) {
            if (!self::isValidMaskGroup($maskGroup, $allowRestrictedMasks))
                return false;

            list($length, $prefixes_str) = explode(':', $maskGroup);
            $prefixes = array_merge($prefixes, explode(',', $prefixes_str));
        }

        // check that 2 prefixes in the masks don't overlap
        if (self::hasOverlappingPrefixes($prefixes))
            return false;

        return true;
    }

    public static function hasGoodSyntax($dialPlan)
    {
        $min_length = config('app.dial_plan.min_length');
        $max_length = config('app.dial_plan.max_length');

        if (!preg_match('/^([' . $min_length . '-' . $max_length . ']:\d+(,\d+)*;)+$/', $dialPlan))
            return false;

        return true;
    }

    protected static function isValidMaskGroup($maskGroup, $allowRestrictedMasks = true)
    {
        if (!preg_match('/^\d:\d+(,\d+)*$/', $maskGroup))
            return false;

        list($length, $prefixes_str) = explode(':', $maskGroup);
        $prefixes = explode(',', $prefixes_str);

        // check that prefix length is not longer than length specified
        foreach ($prefixes as $prefix) {
            if (strlen($prefix) > $length)
                return false;
        }

        // check that 2 prefixes don't overlap
        if (self::hasOverlappingPrefixes($prefixes))
            return false;

        // check if masks are not reserved
        if (!$allowRestrictedMasks) {
            foreach ($prefixes as $prefix) {
                $mask = new DialPlanPrefix($length . ':' . $prefix);
                if ($mask->isRestricted())
                    return false;
            }
        }

        return true;
    }

    public static function hasRestrictedMask($dialPlan)
    {
        if (!self::hasGoodSyntax($dialPlan))
            return false;

        $maskGroups = explode(';', $dialPlan);

        // remove last item of the array because it's empty
        array_pop($maskGroups);

        foreach ($maskGroups as $maskGroup) {
            list ($length, $prefixes_str) = explode(':', $maskGroup);
            foreach (explode(',', $prefixes_str) as $prefix) {
                $mask = new DialPlanMask($length . ':' . $prefix);
                if ($mask->isRestricted())
                    return true;
            }
        }

        return false;
    }

    protected static function hasOverlappingPrefixes(array $prefixes)
    {
        for ($i = 0; $i < count($prefixes); $i++) {
            for ($j = 0; $j < count($prefixes); $j++) {
                //do not compare a prefix to itself
                if ($i == $j)
                    continue;

                if (starts_with($prefixes[$i], $prefixes[$j]))
                    return true;
            }
        }

        return false;
    }

    public static function getReservedMasks()
    {
        $masks = config('app.dial_plan.reserved_masks.fr');
        if (!$masks)
            return [];

        foreach ($masks as $mask) {
            $mask = new DialPlanMask($mask['length'] . ':' . $mask['prefix']);
            foreach ($mask->getIncludingMasks() as $includingMask)
                $reservedMasks[] = $includingMask;
        }

        return self::uniqueMasks($reservedMasks);
    }

    protected static function uniqueMasks(array $masks)
    {
        $uniqueMasks = [];
        foreach ($masks as $mask) {
            if ($mask->inArray($uniqueMasks))
                continue;
            else
                $uniqueMasks[] = $mask;
        }

        return $uniqueMasks;
    }

    public function getDialPlan()
    {
        return $this->dialPlan;
    }

    public function includesExtension($extension)
    {
        return in_array($extension, $this->filterAvailableExtensions());
    }

    public function getMasks()
    {
        $masks = explode(';', $this->dialPlan);
        $ret = [];
        foreach ($masks as $mask) {
            if (!empty($mask))
                $ret[] = new DialPlanMask($mask);
        }
        return $ret;
    }

    public function filterAvailableExtensions($input = null)
    {
        $plans = explode(';', $this->dialPlan);
        $extensions = [];
        $ret = [];

        foreach ($plans as $plan) {
            $split = explode(':', $plan);
            if (count($split) > 1) {
                $n = $split[0];
                $prefixes = explode(',', $split[1]);
                foreach ($prefixes as $prefix) {
                    $pow = pow(10, $n - strlen($prefix));
                    $times = $n - strlen($prefix);
                    $base = $prefix * $pow;
                    for ($i=0; $i < $pow; $i++) {
                        $extensions[] = $base + $i;
                    }
                }
            }
        }
        if ($input) {
            foreach ($extensions as $extension) {
                if (substr($extension, 0, strlen($input)) === $input) {
                    $ret[] = $extension;
                }
            }

            return count($ret) > 0 ? $ret : null;
        }

        return count($extensions) > 0 ? $extensions : null;
    }
}
