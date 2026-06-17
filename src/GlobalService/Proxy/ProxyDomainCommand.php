<?php

namespace my127\Workspace\GlobalService\Proxy;

use my127\Console\Usage\Input;
use my127\Workspace\Types\Workspace\Workspace;
use Symfony\Component\Yaml\Yaml;

class ProxyDomainCommand
{
    public static function domainList(array $domains): void
    {
        echo ProxyDomainConfiguration::dumpDomainList($domains);
    }

    public static function domainAdd(array $domains, Input $input, string $home): void
    {
        $id = $input->argument('id');
        $domain = ProxyDomainConfiguration::createDomain(
            $id,
            self::requiredOption($input, 'name'),
            self::requiredOption($input, 'crt'),
            self::requiredOption($input, 'key'),
            self::optionalOption($input, 'crt-file'),
            self::optionalOption($input, 'key-file')
        );

        if (isset($domains[$id])) {
            throw new \InvalidArgumentException(sprintf('Proxy Domain "%s" already exists. Use update %s to replace it.', $id, $id));
        }

        $registry = new ProxyDomainRegistry($home);
        $registeredDomains = $registry->read();
        if (isset($registeredDomains[$id])) {
            throw new \InvalidArgumentException(sprintf('Proxy Domain "%s" already exists. Use update %s to replace it.', $id, $id));
        }

        ProxyDomainConfiguration::assertNoConflicts($domains, $id, $domain);
        ProxyDomainConfiguration::assertNoConflicts($registeredDomains, $id, $domain);

        $registeredDomains[$id] = $domain;
        $registry->write($registeredDomains);

        echo sprintf("Registered Proxy Domain \"%s\" in %s.\n", $id, $registry->displayPath());
        echo "Run ws global service proxy restart to apply certificate and TLS changes.\n";
    }

    public static function domainUpdate(array $domains, Input $input, string $home, Workspace $workspace): void
    {
        $id = $input->argument('id');
        $domain = ProxyDomainConfiguration::createDomain(
            $id,
            self::requiredOption($input, 'name'),
            self::requiredOption($input, 'crt'),
            self::requiredOption($input, 'key'),
            self::optionalOption($input, 'crt-file'),
            self::optionalOption($input, 'key-file')
        );

        $registry = new ProxyDomainRegistry($home);
        $registeredDomains = $registry->read();

        if (!isset($registeredDomains[$id])) {
            throw new \InvalidArgumentException(sprintf('Proxy Domain "%s" is not registered. Use add %s to register it.', $id, $id));
        }

        self::assertRegistryDomainIsEffective($domains, $id, $registeredDomains[$id], $workspace, $registry->path());
        ProxyDomainConfiguration::assertNoConflicts($domains, $id, $domain);
        ProxyDomainConfiguration::assertNoConflicts($registeredDomains, $id, $domain);

        $registeredDomains[$id] = $domain;
        $registry->write($registeredDomains);

        echo sprintf("Updated Proxy Domain \"%s\" in %s.\n", $id, $registry->displayPath());
        echo "Run ws global service proxy restart to apply certificate and TLS changes.\n";
    }

    public static function domainRemove(array $domains, Input $input, string $home, Workspace $workspace): void
    {
        $id = $input->argument('id');
        ProxyDomainConfiguration::assertValidId($id);

        $registry = new ProxyDomainRegistry($home);
        $registeredDomains = $registry->read();

        if (!isset($registeredDomains[$id])) {
            throw new \InvalidArgumentException(sprintf('Proxy Domain "%s" is not registered.', $id));
        }

        self::assertRegistryDomainIsEffective($domains, $id, $registeredDomains[$id], $workspace, $registry->path());

        unset($registeredDomains[$id]);
        $registry->write($registeredDomains);

        echo sprintf("Removed Proxy Domain \"%s\" from %s.\n", $id, $registry->displayPath());
        echo "Projects using this domain will stop resolving through the Global Proxy until it is registered again.\n";
        echo "Run ws global service proxy restart to apply certificate and TLS changes.\n";
    }

    public static function domainImport(array $domains, Input $input, string $home): void
    {
        $source = $input->argument('source');
        $importDomains = ProxyDomainConfiguration::extractDomains(Yaml::parse(self::readSource($source)) ?? []);

        if ($importDomains === []) {
            throw new \InvalidArgumentException(sprintf('No global.service.proxy.domains map found in "%s".', $source));
        }

        $registry = new ProxyDomainRegistry($home);
        $registeredDomains = $registry->read();
        $imported = 0;
        $unchanged = 0;
        $skipped = 0;

        foreach ($importDomains as $id => $importDomain) {
            if ($id === 'default') {
                fwrite(STDERR, "Skipping reserved Proxy Domain ID \"default\".\n");
                ++$skipped;
                continue;
            }

            try {
                $domain = ProxyDomainConfiguration::normalizeDomain($id, is_array($importDomain) ? $importDomain : []);

                if (isset($domains[$id]) && !isset($registeredDomains[$id])) {
                    if (ProxyDomainConfiguration::normalizeDomain($id, $domains[$id]) == $domain) {
                        fwrite(STDERR, sprintf("Proxy Domain \"%s\" is already configured.\n", $id));
                        ++$unchanged;
                        continue;
                    }

                    fwrite(STDERR, sprintf("Skipping Proxy Domain \"%s\" because it is already configured outside the registry.\n", $id));
                    ++$skipped;
                    continue;
                }

                if (isset($registeredDomains[$id])) {
                    if (ProxyDomainConfiguration::normalizeDomain($id, $registeredDomains[$id]) == $domain) {
                        fwrite(STDERR, sprintf("Proxy Domain \"%s\" is already registered.\n", $id));
                        ++$unchanged;
                        continue;
                    }

                    fwrite(STDERR, sprintf("Skipping Proxy Domain \"%s\" because it is already registered with different values.\n", $id));
                    ++$skipped;
                    continue;
                }

                ProxyDomainConfiguration::assertNoConflicts($domains, $id, $domain);
                ProxyDomainConfiguration::assertNoConflicts($registeredDomains, $id, $domain);

                $registeredDomains[$id] = $domain;
                ++$imported;
            } catch (\Throwable $e) {
                fwrite(STDERR, sprintf("Skipping Proxy Domain \"%s\": %s\n", $id, $e->getMessage()));
                ++$skipped;
            }
        }

        if ($imported === 0 && $unchanged === 0) {
            throw new \InvalidArgumentException(sprintf('No Proxy Domains could be imported from "%s".', $source));
        }

        if ($imported > 0) {
            $registry->write($registeredDomains);
        }

        echo sprintf("Imported %d Proxy Domain(s) into %s.\n", $imported, $registry->displayPath());
        if ($skipped > 0) {
            echo sprintf("Skipped %d Proxy Domain(s); see stderr for details.\n", $skipped);
        }
        echo "Run ws global service proxy restart to apply certificate and TLS changes.\n";
    }

    private static function requiredOption(Input $input, string $name): string
    {
        $value = self::optionalOption($input, $name);

        if ($value === null) {
            throw new \InvalidArgumentException(sprintf('Missing required option --%s.', $name));
        }

        return $value;
    }

    private static function optionalOption(Input $input, string $name): ?string
    {
        $value = $input->option($name);

        if ($value === '') {
            return null;
        }

        return $value;
    }

    private static function assertRegistryDomainIsEffective(array $domains, string $id, array $registeredDomain, Workspace $workspace, string $registryPath): void
    {
        if (
            !isset($domains[$id])
            || ProxyDomainConfiguration::normalizeDomain($id, $domains[$id]) != ProxyDomainConfiguration::normalizeDomain($id, $registeredDomain)
        ) {
            throw new \InvalidArgumentException(sprintf('Proxy Domain "%s" is configured outside the registry. Remove that global config override before using this registry command.', $id));
        }

        $registryPath = realpath($registryPath) ?: $registryPath;
        foreach (['name', 'https.crt', 'https.key', 'crt_file', 'key_file'] as $key) {
            $metadata = $workspace->attributeMetadata(sprintf('global.service.proxy.domains.%s.%s', $id, $key));
            $sources = is_array($metadata) ? ($metadata['source'] ?? []) : [];
            $effectiveSource = self::effectiveAttributeSource($sources);
            $effectiveSource = $effectiveSource === null ? null : (realpath($effectiveSource) ?: $effectiveSource);

            if ($effectiveSource !== $registryPath) {
                throw new \InvalidArgumentException(sprintf('Proxy Domain "%s" is configured outside the registry. Remove that global config override before using this registry command.', $id));
            }
        }
    }

    private static function effectiveAttributeSource(array $sources): ?string
    {
        $effectivePrecedence = null;
        $effectiveSource = null;

        foreach ($sources as $precedence => $source) {
            $precedence = (int) substr($precedence, 1);
            if ($effectivePrecedence === null || $precedence > $effectivePrecedence) {
                $effectivePrecedence = $precedence;
                $effectiveSource = $source;
            }
        }

        return $effectiveSource;
    }

    private static function readSource(string $source): string
    {
        $path = $source;
        if (!preg_match('#^https?://#', $source) && !str_starts_with($source, '/')) {
            $path = getcwd() . '/' . $source;
        }

        $contents = @file_get_contents($path);
        if ($contents === false) {
            throw new \InvalidArgumentException(sprintf('Could not read Proxy Domain import source "%s".', $source));
        }

        return $contents;
    }
}
