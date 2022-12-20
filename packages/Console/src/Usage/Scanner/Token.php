<?php

namespace my127\Console\Usage\Scanner;

class Token
{
    public const T_ARGUMENT_START = 1;  // <
    public const T_ARGUMENT_STOP = 2;  // >
    public const T_REQUIRED_START = 3;  // (
    public const T_REQUIRED_STOP = 4;  // )
    public const T_OPTIONAL_START = 5;  // [
    public const T_OPTIONAL_STOP = 6;  // ]
    public const T_ELLIPSIS = 7;  // ...
    public const T_MUTEX = 8;  // |
    public const T_MINUS = 9;  // -
    public const T_EQUALS = 10; // =
    public const T_WS = 11; // any amount of whitespace
    public const T_STRING = 12; // all other characters that are not reserved
    public const T_EOL = 13; // end of input
    public const T_SHORT_OPTION = 14; // -h
    public const T_OPTION_SEQUENCE = 15; // -iou
    public const T_DOUBLE_DASH = 16; // --
    public const T_SINGLE_DASH = 17; // -
    public const T_LONG_OPTION = 18; // --option-name
    public const T_OPTIONS = 19; // options

    private $tokenToText = [
        self::T_ARGUMENT_START => '<',
        self::T_ARGUMENT_STOP => '>',
        self::T_REQUIRED_START => '(',
        self::T_REQUIRED_STOP => ')',
        self::T_OPTIONAL_START => '[',
        self::T_OPTIONAL_STOP => ']',
        self::T_MUTEX => '|',
        self::T_MINUS => '-',
        self::T_EQUALS => '=',
        self::T_WS => 'WS',
        self::T_STRING => 'STRING',
        self::T_EOL => 'EOL',
        self::T_SHORT_OPTION => 'SHORT_OPTION',
        self::T_ELLIPSIS => '...',
        self::T_DOUBLE_DASH => '--',
        self::T_SINGLE_DASH => '-',
        self::T_LONG_OPTION => 'LONG_OPTION',
    ];

    /**
     * Token Type.
     *
     * @var int
     */
    private $type;

    /**
     * Token Value.
     *
     * @var string
     */
    private $value;

    /**
     * Token.
     *
     * @param int    $type  One of the predefined token constants
     * @param string $value Optional value associated with the token
     */
    public function __construct($type, $value = null)
    {
        $this->type = $type;
        $this->value = is_null($value) ? $this->tokenToText[$type] : $value;
    }

    /**
     * Token Type.
     *
     * @return int
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Token Value.
     *
     * @return string
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * Human readable token.
     *
     * @return string
     */
    public function __toString()
    {
        return sprintf("Token('%s', '%s')", $this->tokenToText[$this->type], $this->value);
    }
}
