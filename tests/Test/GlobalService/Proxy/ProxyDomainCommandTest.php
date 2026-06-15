<?php

namespace my127\Workspace\Tests\Test\GlobalService\Proxy;

use my127\Workspace\Tests\IntegrationTestCase;
use Symfony\Component\Yaml\Yaml;

class ProxyDomainCommandTest extends IntegrationTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->cleanInstalledHome();
        $this->cleanProxyDomainRegistry();
    }

    public function testListsTheBuiltInDefaultProxyDomain(): void
    {
        $process = $this->workspaceCommand('global service proxy config domain list');
        $data = Yaml::parse($process->getOutput());

        self::assertSame([
            'domains' => [
                'default' => [
                    'name' => 'my127.site',
                    'https' => [
                        'crt' => 'https://my127.io/workspace/my127.site.crt',
                        'key' => 'https://my127.io/workspace/my127.site.key',
                    ],
                    'crt_file' => 'my127.site.crt',
                    'key_file' => 'my127.site.key',
                ],
            ],
        ], $data);
    }

    public function testAddsAProxyDomainToTheRegistry(): void
    {
        $this->workspaceCommand(
            'global service proxy config domain add acme ' .
            '--name=domain.site ' .
            '--crt=https://certs.domain.site/fullchain.pem ' .
            '--key=https://certs.domain.site/privkey.pem'
        );

        $registry = Yaml::parseFile($this->registryPath());
        self::assertSame([
            'attributes' => [
                'global' => [
                    'service' => [
                        'proxy' => [
                            'domains' => [
                                'acme' => [
                                    'name' => 'domain.site',
                                    'https' => [
                                        'crt' => 'https://certs.domain.site/fullchain.pem',
                                        'key' => 'https://certs.domain.site/privkey.pem',
                                    ],
                                    'crt_file' => 'domain.site.crt',
                                    'key_file' => 'domain.site.key',
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ], $registry);

        $list = Yaml::parse($this->workspaceCommand('global service proxy config domain list')->getOutput());
        self::assertSame('my127.site', $list['domains']['default']['name']);
        self::assertSame('domain.site', $list['domains']['acme']['name']);
    }

    public function testUpdatesAProxyDomainWithAFullReplacement(): void
    {
        $this->workspaceCommand(
            'global service proxy config domain add acme ' .
            '--name=domain.site ' .
            '--crt=https://certs.domain.site/fullchain.pem ' .
            '--key=https://certs.domain.site/privkey.pem'
        );

        $this->workspaceCommand(
            'global service proxy config domain update acme ' .
            '--name=dev.domain.site ' .
            '--crt=https://certs.domain.site/dev-fullchain.pem ' .
            '--key=https://certs.domain.site/dev-privkey.pem ' .
            '--crt-file=dev-domain.crt ' .
            '--key-file=dev-domain.key'
        );

        $registry = Yaml::parseFile($this->registryPath());
        self::assertSame([
            'name' => 'dev.domain.site',
            'https' => [
                'crt' => 'https://certs.domain.site/dev-fullchain.pem',
                'key' => 'https://certs.domain.site/dev-privkey.pem',
            ],
            'crt_file' => 'dev-domain.crt',
            'key_file' => 'dev-domain.key',
        ], $registry['attributes']['global']['service']['proxy']['domains']['acme']);
    }

    public function testRemovesAProxyDomainFromTheRegistry(): void
    {
        $this->workspaceCommand(
            'global service proxy config domain add acme ' .
            '--name=domain.site ' .
            '--crt=https://certs.domain.site/fullchain.pem ' .
            '--key=https://certs.domain.site/privkey.pem'
        );

        $this->workspaceCommand('global service proxy config domain remove acme');

        $registry = Yaml::parseFile($this->registryPath());
        self::assertArrayNotHasKey('acme', $registry['attributes']['global']['service']['proxy']['domains']);

        $list = Yaml::parse($this->workspaceCommand('global service proxy config domain list')->getOutput());
        self::assertSame(['default'], array_keys($list['domains']));
    }

    public function testImportsProxyDomainsNonDestructively(): void
    {
        $this->workspaceCommand(
            'global service proxy config domain add acme ' .
            '--name=domain.site ' .
            '--crt=https://certs.domain.site/fullchain.pem ' .
            '--key=https://certs.domain.site/privkey.pem'
        );
        $this->workspace()->put('imports/domains.yml', <<<'YAML'
attributes:
  global:
    service:
      proxy:
        domains:
          team:
            name: team.site
            https:
              crt: https://certs.team.site/fullchain.pem
              key: https://certs.team.site/privkey.pem
          default:
            name: ignored.site
            https:
              crt: https://certs.ignored.site/fullchain.pem
              key: https://certs.ignored.site/privkey.pem
          incomplete:
            name: incomplete.site
            https:
              crt: https://certs.incomplete.site/fullchain.pem
YAML);

        $this->workspaceCommand('global service proxy config domain import imports/domains.yml');

        $domains = Yaml::parseFile($this->registryPath())['attributes']['global']['service']['proxy']['domains'];
        self::assertSame(['acme', 'team'], array_keys($domains));
        self::assertSame('domain.site', $domains['acme']['name']);
        self::assertSame([
            'name' => 'team.site',
            'https' => [
                'crt' => 'https://certs.team.site/fullchain.pem',
                'key' => 'https://certs.team.site/privkey.pem',
            ],
            'crt_file' => 'team.site.crt',
            'key_file' => 'team.site.key',
        ], $domains['team']);
    }

    public function testAddRejectsMatchingCertificateAndKeyFilenames(): void
    {
        $process = $this->workspaceProcess(
            'global service proxy config domain add acme ' .
            '--name=domain.site ' .
            '--crt=https://certs.domain.site/fullchain.pem ' .
            '--key=https://certs.domain.site/privkey.pem ' .
            '--crt-file=shared.pem ' .
            '--key-file=shared.pem'
        );

        $process->run();

        self::assertNotSame(0, $process->getExitCode());
        self::assertStringContainsString(
            'certificate and key filenames must be different',
            $process->getOutput() . $process->getErrorOutput()
        );
        self::assertFalse(file_exists($this->registryPath()));
    }

    public function testAddRejectsCaseInsensitiveNameAndFilenameConflicts(): void
    {
        $this->workspaceCommand(
            'global service proxy config domain add acme ' .
            '--name=domain.site ' .
            '--crt=https://certs.domain.site/fullchain.pem ' .
            '--key=https://certs.domain.site/privkey.pem ' .
            '--crt-file=domain.crt ' .
            '--key-file=domain.key'
        );

        $nameConflict = $this->workspaceProcess(
            'global service proxy config domain add other ' .
            '--name=Domain.site ' .
            '--crt=https://certs.other.site/fullchain.pem ' .
            '--key=https://certs.other.site/privkey.pem'
        );
        $nameConflict->run();

        self::assertNotSame(0, $nameConflict->getExitCode());
        self::assertStringContainsString(
            'already uses name "Domain.site"',
            $nameConflict->getOutput() . $nameConflict->getErrorOutput()
        );

        $filenameConflict = $this->workspaceProcess(
            'global service proxy config domain add other ' .
            '--name=other.site ' .
            '--crt=https://certs.other.site/fullchain.pem ' .
            '--key=https://certs.other.site/privkey.pem ' .
            '--crt-file=DOMAIN.CRT ' .
            '--key-file=other.key'
        );
        $filenameConflict->run();

        self::assertNotSame(0, $filenameConflict->getExitCode());
        self::assertStringContainsString(
            'already uses local TLS filename "DOMAIN.CRT"',
            $filenameConflict->getOutput() . $filenameConflict->getErrorOutput()
        );
    }

    public function testImportSkipsDomainsAlreadyConfiguredOutsideTheRegistry(): void
    {
        $this->writeGlobalConfig(<<<'YAML'
attributes:
  global:
    service:
      proxy:
        domains:
          external:
            name: external.site
            https:
              crt: https://certs.external.site/fullchain.pem
              key: https://certs.external.site/privkey.pem
YAML);
        $this->workspace()->put('imports/domains.yml', <<<'YAML'
attributes:
  global:
    service:
      proxy:
        domains:
          external:
            name: changed.site
            https:
              crt: https://certs.changed.site/fullchain.pem
              key: https://certs.changed.site/privkey.pem
          team:
            name: team.site
            https:
              crt: https://certs.team.site/fullchain.pem
              key: https://certs.team.site/privkey.pem
YAML);

        $process = $this->workspaceCommand('global service proxy config domain import imports/domains.yml');

        $domains = Yaml::parseFile($this->registryPath())['attributes']['global']['service']['proxy']['domains'];
        self::assertSame(['team'], array_keys($domains));
        self::assertStringContainsString('Skipped 1 Proxy Domain(s)', $process->getOutput());

        $list = Yaml::parse($this->workspaceCommand('global service proxy config domain list')->getOutput());
        self::assertSame('external.site', $list['domains']['external']['name']);
        self::assertSame('team.site', $list['domains']['team']['name']);
    }

    public function testImportRejectsConflictsWithEffectiveGlobalConfig(): void
    {
        $this->addRegistryDomainShadowedByGlobalOverride();
        $this->workspace()->put('imports/domains.yml', <<<'YAML'
attributes:
  global:
    service:
      proxy:
        domains:
          team:
            name: override.site
            https:
              crt: https://certs.override.site/fullchain.pem
              key: https://certs.override.site/privkey.pem
YAML);

        $process = $this->workspaceProcess('global service proxy config domain import imports/domains.yml');
        $process->run();

        self::assertNotSame(0, $process->getExitCode());
        self::assertStringContainsString(
            'already uses name "override.site"',
            $process->getOutput() . $process->getErrorOutput()
        );

        $domains = Yaml::parseFile($this->registryPath())['attributes']['global']['service']['proxy']['domains'];
        self::assertSame(['acme'], array_keys($domains));
    }

    public function testUpdateFailsWhenARegisteredDomainIsShadowedByAnotherGlobalConfig(): void
    {
        $this->addRegistryDomainShadowedByGlobalOverride();

        $process = $this->workspaceProcess(
            'global service proxy config domain update acme ' .
            '--name=updated.site ' .
            '--crt=https://certs.updated.site/fullchain.pem ' .
            '--key=https://certs.updated.site/privkey.pem'
        );
        $process->run();

        self::assertNotSame(0, $process->getExitCode());
        self::assertStringContainsString(
            'configured outside the registry',
            $process->getOutput() . $process->getErrorOutput()
        );

        $list = Yaml::parse($this->workspaceCommand('global service proxy config domain list')->getOutput());
        self::assertSame('override.site', $list['domains']['acme']['name']);
    }

    public function testRemoveFailsWhenARegisteredDomainIsShadowedByAnotherGlobalConfig(): void
    {
        $this->addRegistryDomainShadowedByGlobalOverride();

        $process = $this->workspaceProcess('global service proxy config domain remove acme');
        $process->run();

        self::assertNotSame(0, $process->getExitCode());
        self::assertStringContainsString(
            'configured outside the registry',
            $process->getOutput() . $process->getErrorOutput()
        );

        $domains = Yaml::parseFile($this->registryPath())['attributes']['global']['service']['proxy']['domains'];
        self::assertArrayHasKey('acme', $domains);
    }

    private function cleanInstalledHome(): void
    {
        $homeDir = $_SERVER['MY127WS_HOME'] . '/.my127/workspace';

        if (!is_dir($homeDir)) {
            return;
        }

        $this->remove($homeDir);
    }

    private function cleanProxyDomainRegistry(): void
    {
        $configDir = dirname($this->registryPath());

        if (!is_dir($configDir)) {
            return;
        }

        foreach (glob($configDir . '/*.yml') as $file) {
            unlink($file);
        }
    }

    private function registryPath(): string
    {
        return $_SERVER['MY127WS_HOME'] . '/.config/my127/workspace/proxy-domains.yml';
    }

    private function writeGlobalConfig(string $contents, string $filename = 'external.yml'): void
    {
        $configDir = $_SERVER['MY127WS_HOME'] . '/.config/my127/workspace';
        if (!is_dir($configDir)) {
            mkdir($configDir, 0755, true);
        }

        file_put_contents($configDir . '/' . $filename, $contents);
    }

    private function addRegistryDomainShadowedByGlobalOverride(): void
    {
        $this->workspaceCommand(
            'global service proxy config domain add acme ' .
            '--name=domain.site ' .
            '--crt=https://certs.domain.site/fullchain.pem ' .
            '--key=https://certs.domain.site/privkey.pem'
        );
        $this->writeGlobalConfig(<<<'YAML'
attributes:
  global:
    service:
      proxy:
        domains:
          acme:
            name: override.site
            https:
              crt: https://certs.override.site/fullchain.pem
              key: https://certs.override.site/privkey.pem
            crt_file: override.site.crt
            key_file: override.site.key
YAML, 'zz-override.yml');
    }

    private function remove(string $path): void
    {
        $node = new \SplFileInfo($path);

        if (in_array($node->getType(), ['socket', 'file', 'link'])) {
            unlink($path);

            return;
        }

        $files = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($path, \RecursiveDirectoryIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::CHILD_FIRST
        );

        foreach ($files as $file) {
            $this->remove($file->getPathName());
        }

        rmdir($path);
    }
}
