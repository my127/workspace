<?php

namespace my127\Workspace\Tests\Test\GlobalService\Proxy;

use my127\Workspace\Tests\IntegrationTestCase;
use my127\Workspace\Utility\Filesystem;
use Symfony\Component\Process\Process;
use Symfony\Component\Yaml\Yaml;

class ProxyRuntimeCommandTest extends IntegrationTestCase
{
    private ?Process $server = null;
    private ?string $serverUrl = null;

    protected function tearDown(): void
    {
        if ($this->server !== null) {
            $this->server->stop();
        }
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->cleanInstalledHome();
        $this->cleanProxyDomainRegistry();
    }

    public function testPrintsTraefikHostRuleForGlobalService(): void
    {
        $this->addCustomDomain();

        self::assertSame(
            "Host(`mail.my127.site`) || Host(`mail.domain.site`)\n",
            $this->workspaceCommand('global service proxy config rule mail')->getOutput()
        );
        self::assertSame(
            "Host(`my127.site`) || Host(`domain.site`)\n",
            $this->workspaceCommand('global service proxy config rule proxy')->getOutput()
        );
    }

    public function testPrintsTraefikTlsConfiguration(): void
    {
        $this->addCustomDomain();

        $tls = Yaml::parse($this->workspaceCommand('global service proxy config tls')->getOutput());

        self::assertSame([
            'certFile' => '/tls/my127.site.crt',
            'keyFile' => '/tls/my127.site.key',
        ], $tls['tls']['stores']['default']['defaultCertificate']);
        self::assertSame([
            [
                'certFile' => '/tls/domain.site.crt',
                'keyFile' => '/tls/domain.site.key',
            ],
        ], $tls['tls']['certificates']);
    }

    public function testWritesTraefikTlsConfigurationToOutputFile(): void
    {
        $this->addCustomDomain();

        $this->workspaceCommand('global service proxy config tls --output=tls.yaml');

        $tls = Yaml::parse($this->workspace()->getContents('tls.yaml'));

        self::assertSame([
            'certFile' => '/tls/my127.site.crt',
            'keyFile' => '/tls/my127.site.key',
        ], $tls['tls']['stores']['default']['defaultCertificate']);
        self::assertSame([
            [
                'certFile' => '/tls/domain.site.crt',
                'keyFile' => '/tls/domain.site.key',
            ],
        ], $tls['tls']['certificates']);
    }

    public function testRejectsRuntimeTlsFilenameCollisions(): void
    {
        $this->writeGlobalConfig(<<<'YAML'
attributes:
  global:
    service:
      proxy:
        domains:
          acme:
            name: domain.site
            https:
              crt: https://certs.domain.site/fullchain.pem
              key: https://certs.domain.site/privkey.pem
            crt_file: shared.pem
            key_file: domain.key
          other:
            name: other.site
            https:
              crt: https://certs.other.site/fullchain.pem
              key: https://certs.other.site/privkey.pem
            crt_file: other.crt
            key_file: SHARED.PEM
YAML);

        $process = $this->workspaceProcess('global service proxy config tls');
        $process->run();

        self::assertNotSame(0, $process->getExitCode());
        self::assertStringContainsString(
            'already uses local TLS filename "SHARED.PEM"',
            $process->getOutput() . $process->getErrorOutput()
        );
    }

    public function testRejectsScalarProxyDomainEntries(): void
    {
        $this->writeGlobalConfig(<<<'YAML'
attributes:
  global:
    service:
      proxy:
        domains:
          acme: disabled
YAML);

        $process = $this->workspaceProcess('global service proxy config rule mail');
        $process->run();

        self::assertNotSame(0, $process->getExitCode());
        $output = $process->getOutput() . $process->getErrorOutput();
        self::assertStringContainsString('Proxy Domain', $output);
        self::assertStringContainsString('"acme" must be a map', $output);
    }

    public function testDownloadsConfiguredCertificates(): void
    {
        $this->startCertificateServer();
        $this->writeGlobalConfig(<<<YAML
attribute('global.service.proxy.https.crt'): {$this->serverUrl}/my127.site.crt
attribute('global.service.proxy.https.key'): {$this->serverUrl}/my127.site.key
YAML);
        $this->workspaceCommand(
            'global service proxy config domain add acme ' .
            '--name=domain.site ' .
            "--crt={$this->serverUrl}/domain.site.crt " .
            "--key={$this->serverUrl}/domain.site.key"
        );

        $this->workspaceCommand('global service proxy config certificates download tls');

        self::assertSame('default certificate', $this->workspace()->getContents('tls/my127.site.crt'));
        self::assertSame('default key', $this->workspace()->getContents('tls/my127.site.key'));
        self::assertSame('custom certificate', $this->workspace()->getContents('tls/domain.site.crt'));
        self::assertSame('custom key', $this->workspace()->getContents('tls/domain.site.key'));
    }

    private function startCertificateServer(): void
    {
        $this->workspace()->put('certs/my127.site.crt', 'default certificate');
        $this->workspace()->put('certs/my127.site.key', 'default key');
        $this->workspace()->put('certs/domain.site.crt', 'custom certificate');
        $this->workspace()->put('certs/domain.site.key', 'custom key');

        $socket = stream_socket_server('tcp://127.0.0.1:0');
        if ($socket === false) {
            throw new \RuntimeException('Could not reserve local HTTP port.');
        }
        $address = stream_socket_get_name($socket, false);
        fclose($socket);

        $this->serverUrl = 'http://' . $address;
        $this->server = new Process([PHP_BINARY, '-S', $address, '-t', $this->workspace()->path('certs')]);
        $this->server->start();

        for ($i = 0; $i < 30; ++$i) {
            if (@file_get_contents($this->serverUrl . '/my127.site.crt') === 'default certificate') {
                return;
            }
            usleep(100000);
        }

        throw new \RuntimeException('Certificate fixture server did not start.');
    }

    private function writeGlobalConfig(string $contents): void
    {
        $configDir = $_SERVER['MY127WS_HOME'] . '/.config/my127/workspace';
        if (!is_dir($configDir)) {
            mkdir($configDir, 0755, true);
        }

        file_put_contents($configDir . '/test.yml', $contents);
    }

    private function addCustomDomain(): void
    {
        $this->workspaceCommand(
            'global service proxy config domain add acme ' .
            '--name=domain.site ' .
            '--crt=https://certs.domain.site/fullchain.pem ' .
            '--key=https://certs.domain.site/privkey.pem'
        );
    }

    private function cleanInstalledHome(): void
    {
        $homeDir = $_SERVER['MY127WS_HOME'] . '/.my127/workspace';

        if (!is_dir($homeDir)) {
            return;
        }

        Filesystem::rrmdir($homeDir);
    }

    private function cleanProxyDomainRegistry(): void
    {
        $configDir = $_SERVER['MY127WS_HOME'] . '/.config/my127/workspace';

        if (!is_dir($configDir)) {
            return;
        }

        foreach (glob($configDir . '/*.yml') as $file) {
            unlink($file);
        }
    }
}
