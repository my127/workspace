<?php

namespace my127\Workspace\GlobalService\Proxy;

use Symfony\Component\Yaml\Yaml;

class ProxyRuntimeConfiguration
{
    public static function traefikRule(array $domains, ?string $hostPrefix): string
    {
        $hostPrefix = self::normalizeHostPrefix($hostPrefix);

        $domains = ProxyDomainConfiguration::normalizeDomains($domains);
        if ($domains === []) {
            throw new \InvalidArgumentException('No valid Proxy Domains are configured.');
        }

        $hosts = [];
        foreach ($domains as $domain) {
            $hosts[] = sprintf('Host(`%s%s`)', $hostPrefix, $domain['name']);
        }

        return implode(' || ', $hosts);
    }

    public static function tlsYaml(array $domains): string
    {
        $domains = ProxyDomainConfiguration::normalizeDomains($domains);
        $default = $domains['default'];

        $tls = [
            'tls' => [
                'stores' => [
                    'default' => [
                        'defaultCertificate' => self::certificateFiles($default),
                    ],
                ],
            ],
        ];

        foreach ($domains as $id => $domain) {
            if ($id === 'default') {
                continue;
            }

            $tls['tls']['certificates'][] = self::certificateFiles($domain);
        }

        return Yaml::dump($tls, 10, 2);
    }

    private static function certificateFiles(array $domain): array
    {
        return [
            'certFile' => '/tls/' . $domain['crt_file'],
            'keyFile' => '/tls/' . $domain['key_file'],
        ];
    }

    private static function normalizeHostPrefix(?string $hostPrefix): string
    {
        if ($hostPrefix === null || $hostPrefix === '') {
            return '';
        }

        $hostPrefix = rtrim($hostPrefix, '.');
        if ($hostPrefix === '' || !preg_match('/^[a-zA-Z0-9](?:[a-zA-Z0-9-]{0,61}[a-zA-Z0-9])?(?:\.[a-zA-Z0-9](?:[a-zA-Z0-9-]{0,61}[a-zA-Z0-9])?)*$/', $hostPrefix)) {
            throw new \InvalidArgumentException('Global Proxy host prefix must be DNS labels without the domain suffix.');
        }

        return $hostPrefix . '.';
    }
}
