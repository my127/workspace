<?php

namespace my127\Workspace\GlobalService\Proxy;

use Symfony\Component\Yaml\Yaml;

class ProxyRuntimeConfiguration
{
    private const SERVICE_HOST_PREFIX = [
        'proxy' => '',
        'mail' => 'mail.',
        'logger' => 'kibana.',
        'tracing' => 'tracing.',
    ];

    public static function traefikRule(array $domains, string $service): string
    {
        if (!isset(self::SERVICE_HOST_PREFIX[$service])) {
            throw new \InvalidArgumentException(sprintf('Unsupported Global Proxy rule service "%s".', $service));
        }

        $domains = ProxyDomainConfiguration::normalizeDomains($domains);
        if ($domains === []) {
            throw new \InvalidArgumentException('No valid Proxy Domains are configured.');
        }

        $hosts = [];
        foreach ($domains as $domain) {
            $hosts[] = sprintf('Host(`%s%s`)', self::SERVICE_HOST_PREFIX[$service], $domain['name']);
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
}
