<?php

namespace my127\Workspace\Tests\Test\Application;

use my127\Workspace\Tests\IntegrationTestCase;

class GlobalProxyConfigurationTest extends IntegrationTestCase
{
    public function testDefaultProxyDomainConfiguration(): void
    {
        $env = $this->isolatedHomeEnvironment();

        self::assertSame("my127.site\n", $this->workspaceCommand('global config get global.service.proxy.domain', null, $env)->getOutput());
        self::assertSame("https://my127.io/workspace/my127.site.crt\n", $this->workspaceCommand('global config get global.service.proxy.https.crt', null, $env)->getOutput());
        self::assertSame("https://my127.io/workspace/my127.site.key\n", $this->workspaceCommand('global config get global.service.proxy.https.key', null, $env)->getOutput());
        self::assertSame("my127.site.crt\n", $this->workspaceCommand('global config get global.service.proxy.https.crt_file', null, $env)->getOutput());
        self::assertSame("my127.site.key\n", $this->workspaceCommand('global config get global.service.proxy.https.key_file', null, $env)->getOutput());
    }

    public function testProxyDomainCanBeOverriddenFromGlobalConfigFile(): void
    {
        $env = $this->isolatedHomeEnvironment(<<<'YAML'
attribute('global.service.proxy.domain'): dev.example.test
attribute('global.service.proxy.https.crt'): https://certs.example.test/dev.example.test.crt
attribute('global.service.proxy.https.key'): https://certs.example.test/dev.example.test.key
YAML
        );

        self::assertSame("dev.example.test\n", $this->workspaceCommand('global config get global.service.proxy.domain', null, $env)->getOutput());
        self::assertSame("https://certs.example.test/dev.example.test.crt\n", $this->workspaceCommand('global config get global.service.proxy.https.crt', null, $env)->getOutput());
        self::assertSame("https://certs.example.test/dev.example.test.key\n", $this->workspaceCommand('global config get global.service.proxy.https.key', null, $env)->getOutput());
        self::assertSame("dev.example.test.crt\n", $this->workspaceCommand('global config get global.service.proxy.https.crt_file', null, $env)->getOutput());
        self::assertSame("dev.example.test.key\n", $this->workspaceCommand('global config get global.service.proxy.https.key_file', null, $env)->getOutput());
    }

    public function testProxyCertificateFilenamesCanBeOverriddenFromGlobalConfigFile(): void
    {
        $env = $this->isolatedHomeEnvironment(<<<'YAML'
attribute('global.service.proxy.domain'): dev.example.test
attribute('global.service.proxy.https.crt_file'): proxy.crt
attribute('global.service.proxy.https.key_file'): proxy.key
YAML
        );

        self::assertSame("proxy.crt\n", $this->workspaceCommand('global config get global.service.proxy.https.crt_file', null, $env)->getOutput());
        self::assertSame("proxy.key\n", $this->workspaceCommand('global config get global.service.proxy.https.key_file', null, $env)->getOutput());
    }

    public function testGlobalServiceComposeFilesUseConfiguredProxyDomainVariable(): void
    {
        $root = dirname(__DIR__, 3);

        self::assertStringContainsString('Host(`${MY127WS_PROXY_DOMAIN:-my127.site}`)', file_get_contents($root . '/home/service/proxy/docker-compose.yml'));
        self::assertStringContainsString('Host(`mail.${MY127WS_PROXY_DOMAIN:-my127.site}`)', file_get_contents($root . '/home/service/mail/docker-compose.yml'));
        self::assertStringContainsString('Host(`kibana.${MY127WS_PROXY_DOMAIN:-my127.site}`)', file_get_contents($root . '/home/service/logger/docker-compose.yml'));
        self::assertStringContainsString('Host(`tracing.${MY127WS_PROXY_DOMAIN:-my127.site}`)', file_get_contents($root . '/home/service/tracing/docker-compose.yml'));
        self::assertFileDoesNotExist($root . '/home/service/proxy/traefik/root/config/tls.yaml');
    }

    public function testDirectMailServicePathExportsConfiguredProxyDomain(): void
    {
        $env = $this->isolatedHomeEnvironment(<<<'YAML'
attribute('global.service.proxy.domain'): dev.example.test
YAML
        );
        $this->workspaceCommand('', null, $env);

        $this->prepareFakeServiceTools($env, <<<'BASH'
#!/bin/bash
echo "$MY127WS_PROXY_DOMAIN" > "$MY127WS_TEST_OUTPUT"
BASH
        );
        $env['MY127WS_TEST_OUTPUT'] = $this->workspace()->path('proxy-domain-output');

        $this->workspace()->put('workspace.yml', <<<'YAML'
command('direct service mail enable'): |
  #!bash
  ws-service mail enable
YAML
        );

        $this->workspaceCommand('direct service mail enable', null, $env);

        self::assertSame("dev.example.test\n", file_get_contents($env['MY127WS_TEST_OUTPUT']));
    }

    public function testDirectMailServicePathIgnoresProjectProxyConfiguration(): void
    {
        $env = $this->isolatedHomeEnvironment(<<<'YAML'
attribute('global.service.proxy.domain'): global.example.test
YAML
        );
        $this->workspaceCommand('', null, $env);

        $this->prepareFakeServiceTools($env, <<<'BASH'
#!/bin/bash
echo "$MY127WS_PROXY_DOMAIN" > "$MY127WS_TEST_OUTPUT"
BASH
        );
        $env['MY127WS_TEST_OUTPUT'] = $this->workspace()->path('proxy-domain-output');

        $this->workspace()->put('workspace.yml', <<<'YAML'
workspace('proxy-test'): ~
attribute.override('global.service.proxy.domain'): project.example.test
command('direct service mail enable'): |
  #!bash
  ws-service mail enable
YAML
        );

        $this->workspaceCommand('direct service mail enable', null, $env);

        self::assertSame("global.example.test\n", file_get_contents($env['MY127WS_TEST_OUTPUT']));
    }

    public function testGlobalProxyServiceCommandUsesGlobalConfigurationFromProject(): void
    {
        $env = $this->isolatedHomeEnvironment(<<<'YAML'
attribute('global.service.proxy.domain'): global.example.test
attribute('global.service.proxy.https.crt'): https://certs.example.test/global.crt
attribute('global.service.proxy.https.key'): https://certs.example.test/global.key
YAML
        );
        $this->workspaceCommand('', null, $env);

        $this->prepareFakeProxyServiceTools($env);
        $env['MY127WS_TEST_OUTPUT'] = $this->workspace()->path('proxy-service-output');

        $this->workspace()->put('workspace.yml', <<<'YAML'
workspace('proxy-test'): ~
attribute.override('global.service.proxy.domain'): project.example.test
attribute.override('global.service.proxy.https.crt'): https://certs.example.test/project.crt
attribute.override('global.service.proxy.https.key'): https://certs.example.test/project.key
YAML
        );

        $this->workspaceCommand('global service proxy restart', null, $env);

        $expected = <<<'TEXT'
global.example.test
https://certs.example.test/global.crt
https://certs.example.test/global.key
global.example.test.crt
global.example.test.key
tls:
  stores:
    default:
      defaultCertificate:
        certFile: /tls/global.example.test.crt
        keyFile: /tls/global.example.test.key
TEXT
        ;
        self::assertSame($expected . "\n", file_get_contents($env['MY127WS_TEST_OUTPUT']));
    }

    public function testGlobalProxyServiceCommandUsesInstalledDefaultsFromProject(): void
    {
        $env = $this->isolatedHomeEnvironment();
        $this->workspaceCommand('', null, $env);

        $this->prepareFakeProxyServiceTools($env);
        $env['MY127WS_TEST_OUTPUT'] = $this->workspace()->path('proxy-service-output');

        $this->createProxyTestWorkspace();

        $this->workspaceCommand('global service proxy restart', null, $env);

        $expected = <<<'TEXT'
my127.site
https://my127.io/workspace/my127.site.crt
https://my127.io/workspace/my127.site.key
my127.site.crt
my127.site.key
tls:
  stores:
    default:
      defaultCertificate:
        certFile: /tls/my127.site.crt
        keyFile: /tls/my127.site.key
TEXT
        ;
        self::assertSame($expected . "\n", file_get_contents($env['MY127WS_TEST_OUTPUT']));
    }

    public function testGlobalProxyServiceCommandRejectsInvalidTlsFilename(): void
    {
        $env = $this->isolatedHomeEnvironment(<<<'YAML'
attribute('global.service.proxy.domain'): global.example.test
attribute('global.service.proxy.https.crt'): https://certs.example.test/global.crt
attribute('global.service.proxy.https.key'): https://certs.example.test/global.key
attribute('global.service.proxy.https.crt_file'): ../global.crt
attribute('global.service.proxy.https.key_file'): global.key
YAML
        );
        $this->workspaceCommand('', null, $env);
        $this->prepareFakeNoopProxyServiceTools($env);

        $this->createProxyTestWorkspace();
        $process = $this->workspaceProcess('global service proxy restart', null, $env);
        $process->run();

        self::assertNotSame(0, $process->getExitCode());
        self::assertStringContainsString('Invalid TLS filename: ../global.crt', $process->getErrorOutput());
    }

    public function testGlobalProxyServiceCommandRejectsSameTlsFilenames(): void
    {
        $env = $this->isolatedHomeEnvironment(<<<'YAML'
attribute('global.service.proxy.domain'): global.example.test
attribute('global.service.proxy.https.crt'): https://certs.example.test/global.crt
attribute('global.service.proxy.https.key'): https://certs.example.test/global.key
attribute('global.service.proxy.https.crt_file'): global.pem
attribute('global.service.proxy.https.key_file'): global.pem
YAML
        );
        $this->workspaceCommand('', null, $env);
        $this->prepareFakeNoopProxyServiceTools($env);

        $this->createProxyTestWorkspace();
        $process = $this->workspaceProcess('global service proxy restart', null, $env);
        $process->run();

        self::assertNotSame(0, $process->getExitCode());
        self::assertStringContainsString('TLS certificate and key filenames must be different.', $process->getErrorOutput());
    }

    private function isolatedHomeEnvironment(?string $globalConfig = null): array
    {
        $home = $this->workspace()->path('home');

        if ($globalConfig !== null) {
            $this->workspace()->put('home/.config/my127/workspace/proxy.yml', $globalConfig);
        }

        return ['MY127WS_HOME' => $home];
    }

    private function createProxyTestWorkspace(): void
    {
        $this->workspace()->put('workspace.yml', "workspace('proxy-test'): ~\n");
    }

    private function prepareFakeProxyServiceTools(array &$env): void
    {
        $dockerComposeScript = <<<'BASH'
#!/bin/bash
{
  printf '%s\n' "$MY127WS_PROXY_DOMAIN"
  printf '%s\n' "$MY127WS_PROXY_HTTPS_CRT"
  printf '%s\n' "$MY127WS_PROXY_HTTPS_KEY"
  printf '%s\n' "$MY127WS_PROXY_HTTPS_CRT_FILE"
  printf '%s\n' "$MY127WS_PROXY_HTTPS_KEY_FILE"
  cat traefik/root/config/tls.yaml
} > "$MY127WS_TEST_OUTPUT"
BASH;

        $this->prepareFakeServiceTools($env, $dockerComposeScript, true);
    }

    private function prepareFakeNoopProxyServiceTools(array &$env): void
    {
        $dockerComposeScript = <<<'BASH'
#!/bin/bash
exit 0
BASH;

        $this->prepareFakeServiceTools($env, $dockerComposeScript, true);
    }

    private function prepareFakeServiceTools(array &$env, string $dockerComposeScript, bool $includeCurl = false): void
    {
        $root = dirname(__DIR__, 3);
        $fakeBin = $this->workspace()->path('fake-bin');
        mkdir($fakeBin);
        symlink($root . '/bin/workspace', $fakeBin . '/ws');
        $this->workspace()->put('fake-bin/docker', <<<'BASH'
#!/bin/bash
exit 0
BASH
        );
        $this->workspace()->put('fake-bin/docker-compose', $dockerComposeScript);
        chmod($this->workspace()->path('fake-bin/docker'), 0755);
        chmod($this->workspace()->path('fake-bin/docker-compose'), 0755);

        if ($includeCurl) {
            $this->workspace()->put('fake-bin/curl', <<<'BASH'
#!/bin/bash
output=""

while [ "$#" -gt 0 ]; do
  if [ "$1" = "--output" ]; then
    shift
    output="$1"
  fi
  shift || true
done

if [ -n "$output" ]; then
  printf 'fake certificate\n' > "$output"
fi
BASH
            );
            chmod($this->workspace()->path('fake-bin/curl'), 0755);
        }

        $env['PATH'] = $fakeBin . ':' . getenv('PATH');
    }
}
