<?php

namespace my127\Workspace\GlobalService\Proxy;

use my127\Console\Usage\Input;

class ProxyRuntimeCommand
{
    public static function rule(array $domains, Input $input): void
    {
        echo ProxyRuntimeConfiguration::traefikRule($domains, $input->argument('service')) . "\n";
    }

    public static function tls(array $domains): void
    {
        echo ProxyRuntimeConfiguration::tlsYaml($domains);
    }

    public static function downloadCertificates(array $domains, Input $input, string $home): void
    {
        $registry = new ProxyDomainRegistry($home);
        (new CertificateDownloader())->download($domains, $input->argument('dir'), $registry->displayPath());
    }
}
