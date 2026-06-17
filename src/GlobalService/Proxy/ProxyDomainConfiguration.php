<?php

namespace my127\Workspace\GlobalService\Proxy;

use Symfony\Component\Yaml\Yaml;

class ProxyDomainConfiguration
{
    public static function assertDomainMap(mixed $domains): array
    {
        if (!is_array($domains)) {
            throw new \InvalidArgumentException('global.service.proxy.domains must be a map.');
        }

        return $domains;
    }

    public static function createDomain(
        string $id,
        string $name,
        string $crt,
        string $key,
        ?string $crtFile = null,
        ?string $keyFile = null,
    ): array {
        self::assertValidId($id);

        $domain = [
            'name' => $name,
            'https' => [
                'crt' => $crt,
                'key' => $key,
            ],
            'crt_file' => $crtFile ?: self::deriveCertificateFilename($name, 'crt'),
            'key_file' => $keyFile ?: self::deriveCertificateFilename($name, 'key'),
        ];

        return self::normalizeDomain($id, $domain);
    }

    public static function dumpDomainList(array $domains): string
    {
        return Yaml::dump(['domains' => self::normalizeDomains($domains)], 10, 2);
    }

    public static function extractDomains(array $data): array
    {
        if (isset($data['attributes']['global']['service']['proxy']['domains'])) {
            $domains = $data['attributes']['global']['service']['proxy']['domains'];

            return is_array($domains) ? $domains : [];
        }

        if (isset($data["attribute('global.service.proxy.domains')"])) {
            $domains = $data["attribute('global.service.proxy.domains')"];

            return is_array($domains) ? $domains : [];
        }

        if (isset($data["attributes('global.service.proxy.domains')"])) {
            throw new \InvalidArgumentException("Use attribute('global.service.proxy.domains'), not attributes('global.service.proxy.domains').");
        }

        return [];
    }

    public static function normalizeDomains(array $domains): array
    {
        if (!isset($domains['default'])) {
            $domains = array_replace_recursive([
                'default' => [
                    'name' => 'my127.site',
                    'https' => [
                        'crt' => 'https://my127.io/workspace/my127.site.crt',
                        'key' => 'https://my127.io/workspace/my127.site.key',
                    ],
                    'crt_file' => 'my127.site.crt',
                    'key_file' => 'my127.site.key',
                ],
            ], $domains);
        }

        foreach ($domains as $id => $domain) {
            if (!is_array($domain)) {
                throw new \InvalidArgumentException(sprintf('Proxy Domain "%s" must be a map.', $id));
            }

            $domains[$id] = self::normalizeDomain($id, $domain, $id === 'default');
        }

        self::assertNoDomainCollectionConflicts($domains);

        return $domains;
    }

    public static function normalizeDomain(string $id, array $domain, bool $allowDefault = false): array
    {
        self::assertValidId($id, $allowDefault);

        if (isset($domain['name'])) {
            $domain['crt_file'] = $domain['crt_file'] ?? self::deriveCertificateFilename($domain['name'], 'crt');
            $domain['key_file'] = $domain['key_file'] ?? self::deriveCertificateFilename($domain['name'], 'key');
        }

        self::assertValidDomain($id, $domain);

        return $domain;
    }

    public static function assertNoConflicts(array $domains, string $id, array $domain): void
    {
        foreach (self::normalizeDomains($domains) as $existingId => $existingDomain) {
            if ($existingId === $id) {
                continue;
            }

            if (strcasecmp($existingDomain['name'], $domain['name']) === 0) {
                throw new \InvalidArgumentException(sprintf('Proxy Domain "%s" already uses name "%s".', $existingId, $domain['name']));
            }

            foreach (['crt_file', 'key_file'] as $existingKey) {
                foreach (['crt_file', 'key_file'] as $newKey) {
                    if (strcasecmp($existingDomain[$existingKey], $domain[$newKey]) === 0) {
                        throw new \InvalidArgumentException(sprintf('Proxy Domain "%s" already uses local TLS filename "%s".', $existingId, $domain[$newKey]));
                    }
                }
            }
        }
    }

    public static function assertValidId(string $id, bool $allowDefault = false): void
    {
        if ($id === 'default' && !$allowDefault) {
            throw new \InvalidArgumentException('Proxy Domain ID "default" is reserved.');
        }

        if (!preg_match('/^[a-zA-Z0-9_-]+$/', $id)) {
            throw new \InvalidArgumentException('Proxy Domain ID must match ^[a-zA-Z0-9_-]+$.');
        }
    }

    public static function assertValidDomain(string $id, array $domain): void
    {
        foreach (['name', 'https', 'crt_file', 'key_file'] as $key) {
            if (!isset($domain[$key])) {
                throw new \InvalidArgumentException(sprintf('Proxy Domain "%s" is missing "%s".', $id, $key));
            }
        }

        if (!is_array($domain['https'])) {
            throw new \InvalidArgumentException(sprintf('Proxy Domain "%s" is missing certificate sources.', $id));
        }

        foreach (['crt', 'key'] as $key) {
            if (empty($domain['https'][$key])) {
                throw new \InvalidArgumentException(sprintf('Proxy Domain "%s" is missing "https.%s".', $id, $key));
            }
            if (!preg_match('#^https?://#', $domain['https'][$key])) {
                throw new \InvalidArgumentException(sprintf('Proxy Domain "%s" https.%s must be an http:// or https:// URL.', $id, $key));
            }
        }

        if (!self::isBareDnsSuffix($domain['name'])) {
            throw new \InvalidArgumentException(sprintf('Proxy Domain "%s" name must be a bare DNS suffix.', $id));
        }

        foreach (['crt_file', 'key_file'] as $key) {
            if (!self::isSimpleFilename($domain[$key])) {
                throw new \InvalidArgumentException(sprintf('Proxy Domain "%s" %s must be a simple basename.', $id, $key));
            }
        }

        if (strcasecmp($domain['crt_file'], $domain['key_file']) === 0) {
            throw new \InvalidArgumentException(sprintf('Proxy Domain "%s" certificate and key filenames must be different.', $id));
        }
    }

    private static function assertNoDomainCollectionConflicts(array $domains): void
    {
        $names = [];
        $files = [];

        foreach ($domains as $id => $domain) {
            $name = strtolower($domain['name']);
            if (isset($names[$name])) {
                throw new \InvalidArgumentException(sprintf('Proxy Domain "%s" already uses name "%s".', $names[$name], $domain['name']));
            }
            $names[$name] = $id;

            foreach (['crt_file', 'key_file'] as $key) {
                $file = strtolower($domain[$key]);
                if (isset($files[$file])) {
                    throw new \InvalidArgumentException(sprintf('Proxy Domain "%s" already uses local TLS filename "%s".', $files[$file], $domain[$key]));
                }
                $files[$file] = $id;
            }
        }
    }

    private static function deriveCertificateFilename(string $domain, string $extension): string
    {
        return $domain . '.' . $extension;
    }

    private static function isBareDnsSuffix(string $domain): bool
    {
        return (bool) preg_match('/^(?=.{1,253}$)([a-zA-Z0-9](?:[a-zA-Z0-9-]{0,61}[a-zA-Z0-9])?\.)+[a-zA-Z]{2,63}$/', $domain);
    }

    private static function isSimpleFilename(string $filename): bool
    {
        return $filename !== ''
            && $filename !== '.'
            && $filename !== '..'
            && basename($filename) === $filename
            && !str_contains($filename, '/')
            && !str_contains($filename, '\\');
    }
}
