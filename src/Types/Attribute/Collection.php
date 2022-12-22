<?php

namespace my127\Workspace\Types\Attribute;

use my127\Workspace\Expression\Expression;
use my127\Workspace\Utility\Arr;

class Collection implements \ArrayAccess
{
    /** @var mixed[][] */
    private $attributes = [];

    /** @var mixed[][] */
    private $attributeMetadata = [];

    /** @var Expression */
    private $expression;

    /** @var mixed[][]|null */
    private $cache = null;

    public function __construct(Expression $expression)
    {
        $this->expression = $expression;
    }

    public function add(array $attributes, string $source, int $precedence = 1): void
    {
        if (!isset($this->attributes[$precedence])) {
            $this->attributes[$precedence] = [];
        }

        $this->cache = null;
        $this->attributes[$precedence] = array_replace_recursive($this->attributes[$precedence], $attributes);
        $this->attributeMetadata = array_merge_recursive(
            $this->attributeMetadata,
            array_fill_keys(
                $this->getAllAttributeKeys($attributes), ['source' => ['_' . $precedence => $source]]
            )
        );
        array_walk(
            $this->attributeMetadata,
            function (&$value) {
                ksort($value['source']);
            }
        );
    }

    public function get(string $key, $default = null)
    {
        if ($this->cache === null) {
            $this->buildAttributeCache();
        }

        $value = Arr::get($this->cache, $key, $default);

        if ($this->isExpression($value)) {
            $this->evaluate($value);

            return $value;
        }

        if (is_array($value)) {
            array_walk_recursive($value, function (&$value) {
                if ($this->isExpression($value)) {
                    $this->evaluate($value);
                }
            });
        }

        return $value;
    }

    public function set(string $key, $value, string $source, int $precedence = 1): void
    {
        $attributes = [];

        Arr::set($attributes, $key, $value);

        $this->add($attributes, $source, $precedence);
    }

    private function evaluate(&$value): void
    {
        $value = $this->expression->evaluate(substr($value, 1));
    }

    private function isExpression($value): bool
    {
        return is_string($value) && $value != '' && $value[0] == '=';
    }

    public function offsetExists($offset): bool
    {
        if ($this->cache === null) {
            $this->buildAttributeCache();
        }

        $array = &$this->cache;

        if (strpos($offset, '.') === false) {
            return array_key_exists($offset, $array);
        }

        $segments = explode('.', $offset);
        krsort($segments);

        while (($segment = array_pop($segments)) !== null) {
            if (!array_key_exists($segment, $array)) {
                return false;
            }

            $array = &$array[$segment];
        }

        return true;
    }

    public function offsetGet($offset): mixed
    {
        return $this->get($offset);
    }

    public function offsetSet($offset, $value): void
    {
        throw new \Exception('Do not set attribute values via ArrayAccess as source cannot be specified.');
    }

    public function offsetUnset($offset): void
    {
        foreach ($this->attributes as &$attributes) {
            Arr::forget($attributes, $offset);
        }
    }

    private function buildAttributeCache()
    {
        ksort($this->attributes);
        $this->cache = [];

        foreach ($this->attributes as &$attributes) {
            $this->cache = array_replace_recursive($this->cache, $attributes);
        }
    }

    public function getAttributeMetadata(string $key): mixed
    {
        return $this->attributeMetadata[$key] ?? null;
    }

    private function getAllAttributeKeys($attributes, $parent = null): array
    {
        $keys = [];

        foreach ($attributes as $k => $v) {
            $currentKey = is_null($parent) ? $k : $parent . '.' . $k;
            $keys[] = $currentKey;
            if (is_array($v)) {
                $keys = array_merge($keys, $this->getAllAttributeKeys($v, $currentKey));
            } else {
                $keys[] = $currentKey;
            }
        }

        return $keys;
    }
}
