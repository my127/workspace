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

        $root = dirname(__DIR__, 3);
        $fakeBin = $this->workspace()->path('fake-bin');
        mkdir($fakeBin);
        symlink($root . '/bin/workspace', $fakeBin . '/ws');
        $this->workspace()->put('fake-bin/docker', <<<'BASH'
#!/bin/bash
exit 0
BASH
        );
        $this->workspace()->put('fake-bin/docker-compose', <<<'BASH'
#!/bin/bash
echo "$MY127WS_PROXY_DOMAIN" > "$MY127WS_TEST_OUTPUT"
BASH
        );
        chmod($this->workspace()->path('fake-bin/docker'), 0755);
        chmod($this->workspace()->path('fake-bin/docker-compose'), 0755);
        $env['PATH'] = $fakeBin . ':' . getenv('PATH');
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

    public function testGlobalProxyServiceCommandPassesProjectProxyConfiguration(): void
    {
        $env = $this->isolatedHomeEnvironment();
        $this->workspaceCommand('', null, $env);

        $env['MY127WS_TEST_OUTPUT'] = $this->workspace()->path('proxy-service-output');
        $this->writeFakeWsServiceRecorder();

        $this->workspace()->put('workspace.yml', <<<'YAML'
attribute('global.service.proxy.domain'): project.example.test
attribute('global.service.proxy.https.crt'): https://certs.example.test/project.crt
attribute('global.service.proxy.https.key'): https://certs.example.test/project.key
attribute('global.service.proxy.https.crt_file'): project.crt
attribute('global.service.proxy.https.key_file'): project.key
YAML
        );

        $this->workspaceCommand('global service proxy restart', null, $env);

        $expected = <<<'TEXT'
proxy restart
project.example.test
https://certs.example.test/project.crt
https://certs.example.test/project.key
project.crt
project.key
TEXT
        ;
        self::assertSame($expected . "\n", file_get_contents($env['MY127WS_TEST_OUTPUT']));
    }

    public function testGlobalProxyServiceCommandAllowsShellEnvironmentOverride(): void
    {
        $env = $this->isolatedHomeEnvironment();
        $this->workspaceCommand('', null, $env);

        $env['MY127WS_TEST_OUTPUT'] = $this->workspace()->path('proxy-service-output');
        $env['MY127WS_PROXY_DOMAIN'] = 'shell.example.test';
        $env['MY127WS_PROXY_HTTPS_CRT'] = 'https://certs.example.test/shell.crt';
        $env['MY127WS_PROXY_HTTPS_KEY'] = 'https://certs.example.test/shell.key';
        $env['MY127WS_PROXY_HTTPS_CRT_FILE'] = 'shell.crt';
        $env['MY127WS_PROXY_HTTPS_KEY_FILE'] = 'shell.key';
        $this->writeFakeWsServiceRecorder();

        $this->workspace()->put('workspace.yml', <<<'YAML'
attribute('global.service.proxy.domain'): project.example.test
attribute('global.service.proxy.https.crt'): https://certs.example.test/project.crt
attribute('global.service.proxy.https.key'): https://certs.example.test/project.key
attribute('global.service.proxy.https.crt_file'): project.crt
attribute('global.service.proxy.https.key_file'): project.key
YAML
        );

        $this->workspaceCommand('global service proxy restart', null, $env);

        $expected = <<<'TEXT'
proxy restart
shell.example.test
https://certs.example.test/shell.crt
https://certs.example.test/shell.key
shell.crt
shell.key
TEXT
        ;
        self::assertSame($expected . "\n", file_get_contents($env['MY127WS_TEST_OUTPUT']));
    }

    private function isolatedHomeEnvironment(?string $globalConfig = null): array
    {
        $home = $this->workspace()->path('home');

        if ($globalConfig !== null) {
            $this->workspace()->put('home/.config/my127/workspace/proxy.yml', $globalConfig);
        }

        return ['MY127WS_HOME' => $home];
    }

    private function writeFakeWsServiceRecorder(): void
    {
        $this->workspace()->put('home/.my127/workspace/bin/ws-service', <<<'BASH'
#!/bin/bash
{
  printf '%s %s\n' "$1" "$2"
  printf '%s\n' "$MY127WS_PROXY_DOMAIN"
  printf '%s\n' "$MY127WS_PROXY_HTTPS_CRT"
  printf '%s\n' "$MY127WS_PROXY_HTTPS_KEY"
  printf '%s\n' "$MY127WS_PROXY_HTTPS_CRT_FILE"
  printf '%s\n' "$MY127WS_PROXY_HTTPS_KEY_FILE"
} > "$MY127WS_TEST_OUTPUT"
BASH
        );
        chmod($this->workspace()->path('home/.my127/workspace/bin/ws-service'), 0755);
    }
}
