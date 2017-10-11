<?php

namespace common\components;

class Component extends \yii\base\Component
{
    const INTEGER_PATTERN = '/^\s*[+-]?\d+\s*$/';
    const POSITIVE_INTEGER_PATTERN = '/^\d+$/';
    
    /**
     * Converts a string value into integer value
     * @param string $value the value
     * @return integer the result value
     * @throws \InvalidArgumentException if the value has
     * invalid format.
     */
    public static function toInt($value)
    {
        if (is_int($value) || is_integer($value)) {
            return $value;
        }
        if (is_string($value)) {
            if (preg_match(self::INTEGER_PATTERN, $value)) {
                return (int)$value;
            }
        }
        throw new \InvalidArgumentException('Value is invalid.');
    }
    
    /**
     * Converts a string value into positive integer value
     * @param string $value the value
     * @return integer the result value
     * @throws \InvalidArgumentException if the value has
     * invalid format.
     */
    public static function toPositiveInt($value)
    {
        if (is_int($value) || is_integer($value)) {
            return $value;
        }
        if (is_string($value)) {
            if (preg_match(self::POSITIVE_INTEGER_PATTERN, $value)) {
                return (int)$value;
            }
        }
        throw new \InvalidArgumentException('Value is invalid.');
    }
}