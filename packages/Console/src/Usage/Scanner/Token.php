<?php

namespace my127\Console\Usage\Scanner;

class Token
{
    const T_ARGUMENT_START  = 1;  // <
    const T_ARGUMENT_STOP   = 2;  // >
    const T_REQUIRED_START  = 3;  // (
    const T_REQUIRED_STOP   = 4;  // )
    const T_OPTIONAL_START  = 5;  // [
    const T_OPTIONAL_STOP   = 6;  // ]
    const T_ELLIPSIS        = 7;  // ...
    const T_MUTEX           = 8;  // |
    const T_MINUS           = 9;  // -
    const T_EQUALS          = 10; // =
    const T_WS              = 11; // any amount of whitespace
    const T_STRING          = 12; // all other characters that are not reserved
    const T_EOL             = 13; // end of input
    const T_SHORT_OPTION    = 14; // -h
    const T_OPTION_SEQUENCE = 15; // -iou
    const T_DOUBLE_DASH     = 16; // --
    const T_SINGLE_DASH     = 17; // -
    const T_LONG_OPTION     = 18; // --option-name
    const T_OPTIONS         = 19; // options

    private $tokenToText = [
        self::T_ARGUMENT_START => '<',
        self::T_ARGUMENT_STOP  => '>',
        self::T_REQUIRED_START => '(',
        self::T_REQUIRED_STOP  => ')',
        self::T_OPTIONAL_START => '[',
        self::T_OPTIONAL_STOP  => ']',
        self::T_MUTEX          => '|',
        self::T_MINUS          => '-',
        self::T_EQUALS         => '=',
        self::T_WS             => 'WS',
        self::T_STRING         => 'STRING',
        self::T_EOL            => 'EOL',
        self::T_SHORT_OPTION   => 'SHORT_OPTION',
        self::T_ELLIPSIS       => '...',
        self::T_DOUBLE_DASH    => '--',
        self::T_SINGLE_DASH    => '-',
        self::T_LONG_OPTION    => 'LONG_OPTION'
    ];

    /**
     * Token Type
     *
     * @var int
     */

    private $type;

    /**
     * Token Value
     *
     * @var string
     */
    private $value;

    /**
     * Token
     *
     * @param int    $type  One of the predefined token constants
     * @param string $value Optional value associated with the token
     */
    public function __construct($type, $value = null)
    {
        $this->type  = $type;
        $this->value = is_null($value) ? $this->tokenToText[$type] : $value;
    }

    /**
     * Token Type
     *
     * @return int
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Token Value
     *
     * @return string
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * Human readable token
     *
     * @return string
     */
    public function __toString()
    {
        return sprintf("Token('%s', '%s')", $this->tokenToText[$this->type], $this->value);
    }
}
