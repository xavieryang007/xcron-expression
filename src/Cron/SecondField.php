<?php

namespace Cron;

use DateTime;


/**
 * Second field.  Allows: * , / -
 */
class SecondField extends AbstractField
{
    protected $rangeStart = 0;
    protected $rangeEnd = 59;

    public function isSatisfiedBy(DateTime $date, $value)
    {
        return $this->isSatisfied($date->format('s'), $value);
    }

    public function increment(DateTime $date, $invert = false, $parts = null)
    {
        if (is_null($parts)) {
            if ($invert) {
                $date->modify('-1 second');
            } else {
                $date->modify('+1 second');
            }
            return $this;
        }

        $parts = strpos($parts, ',') !== false ? explode(',', $parts) : array($parts);
        $minutes = array();
        foreach ($parts as $part) {
            $minutes = array_merge($minutes, $this->getRangeForExpression($part, 59));
        }

        $current_minute = $date->format('s');
        $position = $invert ? count($minutes) - 1 : 0;
        if (count($minutes) > 1) {
            for ($i = 0; $i < count($minutes) - 1; $i++) {
                if ((!$invert && $current_minute >= $minutes[$i] && $current_minute < $minutes[$i + 1]) ||
                    ($invert && $current_minute > $minutes[$i] && $current_minute <= $minutes[$i + 1])) {
                    $position = $invert ? $i : $i + 1;
                    break;
                }
            }
        }

        if ((!$invert && $current_minute >= $minutes[$position]) || ($invert && $current_minute <= $minutes[$position])) {
            $date->modify(($invert ? '-' : '+') . '1 minute');
            $date->setTime($date->format('i'), $invert ? 59 : 0);
        }
        else {
            $date->setTime($date->format('i'), $minutes[$position]);
        }

        return $this;
    }
}
