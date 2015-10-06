<?php

namespace Method\Common\Traits;

DEFINE('JSON_FULL',     0b0000);
DEFINE('JSON_COMPACT',  0b0001);
DEFINE('JSON_MINIMAL',  0b0010);

trait JsonModes
{
    const JSON_FULL     = 0b0000;
    const JSON_COMPACT  = 0b0001;
    const JSON_MINIMAL  = 0b0010;

    public static $JsonMode = JSON_FULL;
    
    public static function SetJsonMode($modeFlags)
    {
        $class = get_called_class();
        if ($modeFlags === JSON_FULL) {
            $class::$JsonMode = JSON_FULL;
        } else {
            $class::$JsonMode = $modeFlags;
        }
    }

    public static function AddJsonMode($modeFlags)
    {
        $class = get_called_class();
        $class::$JsonMode |= $modeFlags;
    }

    public static function RemoveJsonMode($modeFlags)
    {
        $class = get_called_class();
        $class::$JsonMode &= ~$modeFlags;
    }

    public static function IsJsonModeSet($modeFlags)
    {
        $class = get_called_class();
        if ($modeFlags === JSON_FULL) {
            return $class::$JsonMode === JSON_FULL;
        }
        return ($class::$JsonMode & $modeFlags) === $modeFlags;
    }

    public static function ResetJsonMode()
    {
        $class = get_called_class();
        $class::$JsonMode = JSON_FULL;
    }
}
