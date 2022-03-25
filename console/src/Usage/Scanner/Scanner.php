<?php

namespace my127\Console\Usage\Scanner;

use Exception;

/**
 * Command & Usage Scanner
 */
class Scanner
{
    private $text;
    private $length;
    private $i;

    private $reserved = [
        '(' => true,
        ')' => true,
        '[' => true,
        ']' => true,
        '|' => true,
        '=' => true,
        '<' => true,
        '>' => true,
        '.' => true,
        ' ' => true
    ];

    /**
     * @var \Generator
     */
    private $generator;

    /**
     * Peeked tokens
     *
     * @var Token
     */
    private $peeked = null;

    /**
     * Command & Usage Scanner
     *
     * @param string $text
     */
    public function __construct($text)
    {
        $this->text      = $text;
        $this->length    = strlen($text);
        $this->generator = $this->getGenerator();
    }

    /**
     * Peek Next Token
     *
     * @return Token
     */
    public function peek()
    {
        return $this->peeked?:$this->peeked = $this->pop();
    }

    /**
     * Fetch Next Token
     *
     * @return Token
     */
    public function pop()
    {
        if ($token = $this->peeked) {
            $this->peeked = null;
            return $token;
        }

        $token = $this->generator->current();
        $this->generator->next();
        return $token;
    }

    public function current()
    {
        return $this->generator->current();
    }

    private function getGenerator()
    {
        for ($this->i = 0; $this->i < $this->length; ++$this->i) {
            $token = null;

            switch ($this->text[$this->i]) {
                case ' ':
                    $this->only(' ');
                    break;

                case '(':
                    $token = new Token(Token::T_REQUIRED_START, '(');
                    break;

                case ')':
                    $token = new Token(Token::T_REQUIRED_STOP, ')');
                    break;

                case '[':
                    $token = new Token(Token::T_OPTIONAL_START, '[');
                    break;

                case ']':
                    $token = new Token(Token::T_OPTIONAL_STOP, ']');
                    break;

                case '<':
                    $token = new Token(Token::T_ARGUMENT_START, '<');
                    break;

                case '>':
                    $token = new Token(Token::T_ARGUMENT_STOP, '>');
                    break;

                case '|':
                    $token = new Token(Token::T_MUTEX, '|');
                    break;

                case '=':
                    $token = new Token(Token::T_EQUALS, '=');
                    break;

                case '-':
                    ++$this->i;

                    if ($this->i == $this->length) {
                        $token = new Token(Token::T_SINGLE_DASH, '-');
                        break;
                    }

                    switch (true) {
                        case $this->is('-'):
                            // long option or double dash

                            if (($this->i >= $this->length) || $this->is(' ')) {
                                $token = new Token(Token::T_DOUBLE_DASH, '--');
                                break;
                            }

                            $optionName = '';

                            while (($this->i < $this->length) && (($c = $this->isAlphanumeric()) || ($c = $this->is('-')) || ($c = $this->is('_')))) {
                                $optionName .= $c;
                            }

                            if (strlen($optionName) == 0 || $optionName[0] == '-') {
                                throw new Exception('Expecting long option name');
                            }

                            $token = new Token(Token::T_LONG_OPTION, $optionName);

                            --$this->i;

                            break;

                        case $this->isLetter():
                            // short option or sequence of options

                            --$this->i;

                            $sequence = '';

                            while (($this->i < $this->length) && ($letter = $this->isLetter()) !== false) {
                                $sequence .= $letter;
                            }

                            $token = (strlen($sequence) == 1) ?
                                new Token(Token::T_SHORT_OPTION, $sequence) :
                                new Token(Token::T_OPTION_SEQUENCE, $sequence);

                            --$this->i;

                            break;

                        default:
                            --$this->i;
                            $token = new Token(Token::T_SINGLE_DASH, '-');
                    }

                    break;

                case '.':
                    if (strlen($this->only('.')) != 3) {
                        throw new Exception("Unexpected Character");
                    }

                    $token = new Token(Token::T_ELLIPSIS, '...');

                    break;

                default:
                    $text  = $this->until($this->reserved);
                    $token = new Token(strtoupper($text) == 'OPTIONS' ? Token::T_OPTIONS : TOKEN::T_STRING, $text);
            }

            if (!$token) {
                continue;
            }

            yield $token;
        }

        yield new Token(Token::T_EOL);
    }

    /**
     * isLetter
     *
     * Test and consume if current position is a letter, otherwise
     * return false.
     *
     * @return string|false  Current character if letter, otherwise false
     */
    private function isLetter()
    {
        if (!ctype_alpha($letter = $this->text[$this->i])) {
            return false;
        }

        ++$this->i;

        return $letter;
    }

    /**
     * @return bool
     */
    private function isAlphanumeric()
    {
        if (!ctype_alnum($char = $this->text[$this->i])) {
            return false;
        }

        ++$this->i;

        return $char;
    }

    /**
     * is
     *
     * Test and consume if current position is a match, otherwise
     * return false.
     *
     * @param string $match Single character to match against
     *
     * @return string|false  Current character if match, otherwise false
     */
    private function is($match)
    {
        if (($letter = $this->text[$this->i]) != $match) {
            return false;
        }

        ++$this->i;

        return $letter;
    }

    /**
     * Consume characters only while they match
     *
     * @param $seek
     *
     * @return string
     */
    private function only($seek)
    {
        $seek  = (is_array($seek))?$seek:[$seek => true];
        $value = '';

        while (($this->i < $this->length) && (isset($seek[$char = $this->text[$this->i]]))) {
            $value .= $char;
            ++$this->i;
        }

        --$this->i;

        return $value;
    }

    /**
     * Consume characters until we find a match
     *
     * @param $seek
     *
     * @return string
     */
    private function until($seek)
    {
        $seek  = (is_array($seek))?$seek:[$seek => true];
        $value = '';

        while (($this->i < $this->length) && (!isset($seek[$char = $this->text[$this->i]]))) {
            $value .= $char;
            ++$this->i;
        }

        --$this->i;

        return $value;
    }
}
