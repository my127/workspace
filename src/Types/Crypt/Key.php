<?php

namespace my127\Workspace\Types\Crypt;

class Key
{
    private $name;
    private $key;

    public function __construct(string $name, $key = null)
    {
        $this->name = $name;
        $this->key  = ($key) ? sodium_hex2bin($key) : random_bytes(SODIUM_CRYPTO_SECRETBOX_KEYBYTES);
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getKey(): string
    {
        return $this->key;
    }

    public function getKeyAsHex(): string
    {
        return sodium_bin2hex($this->key);
    }
}
