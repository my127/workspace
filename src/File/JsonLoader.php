<?php

namespace my127\Workspace\File;

use JsonException;
use my127\Workspace\File\Exception\CouldNotDecodeJson;

final class JsonLoader
{
    /**
     * @var FileLoader
     */
    private $loader;

    public function __construct(FileLoader $loader)
    {
        $this->loader = $loader;
    }

    public function loadArray(string $url): array
    {
        $contents = $this->loader->load($url);
        try {
            $decoded = json_decode($contents, true, 512, JSON_THROW_ON_ERROR);
        } catch (JsonException $error) {
            throw new CouldNotDecodeJson(sprintf('Could not decode JSON from "%s": %s', $url, $error->getMessage()), 0, $error);
        }

        return $decoded;
    }
}
