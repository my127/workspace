<?php

namespace my127\Workspace\GlobalService\Proxy;

use my127\Console\Usage\Input;

class ProxyRuntimeCommand
{
    public static function rule(array $domains, Input $input): void
    {
        echo ProxyRuntimeConfiguration::traefikRule($domains, $input->argument('service')) . "\n";
    }

    public static function tls(array $domains, Input $input): void
    {
        $yaml = ProxyRuntimeConfiguration::tlsYaml($domains);
        $output = $input->option('output');

        if ($output === '') {
            echo $yaml;

            return;
        }

        if (file_put_contents($output, $yaml) === false) {
            throw new \RuntimeException(sprintf('Could not write Traefik TLS configuration to "%s".', $output));
        }
    }

    public static function downloadCertificates(array $domains, Input $input, string $home): void
    {
        $registry = new ProxyDomainRegistry($home);
        (new CertificateDownloader())->download($domains, $input->argument('dir'), $registry->displayPath());
    }
}
