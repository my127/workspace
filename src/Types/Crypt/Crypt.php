<?php

namespace my127\Workspace\Types\Crypt;

use Exception;

class Crypt
{
    /** @var Key[] */
    private $keys;

    public function addKey(Key $key): void
    {
        $this->keys[$key->getName()] = $key;
    }

    public function getKey($name): string
    {
        if (!isset($this->keys[$name])) {
            throw new Exception("Key '{$name}' does not exist.");
        }

        return $this->keys[$name]->getKey();
    }

    public function encrypt($message, $key = 'default'): string
    {
        return base64_encode(serialize([
            $key,
            $nonce = random_bytes(SODIUM_CRYPTO_SECRETBOX_NONCEBYTES),
            sodium_crypto_secretbox($message, $nonce, $this->getKey($key)),
        ]));
    }

    public function decrypt($encrypted)
    {
        list($key, $nonce, $ciphertext) = unserialize(base64_decode($encrypted));

        if (($message = sodium_crypto_secretbox_open($ciphertext, $nonce, $this->getKey($key))) !== false) {
            return $message;
        }

        throw new Exception("Unable to decrypt '{$encrypted}' using key '{$key}'");
    }
}
