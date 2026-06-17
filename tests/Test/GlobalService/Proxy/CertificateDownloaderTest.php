<?php

namespace my127\Workspace\GlobalService\Proxy {
    class CertificateDownloaderWriteFailure
    {
        public static bool $enabled = false;
    }

    function file_put_contents($filename, $data, $flags = 0, $context = null)
    {
        if (CertificateDownloaderWriteFailure::$enabled && str_contains(basename($filename), 'proxy-domain-')) {
            \file_put_contents($filename, substr($data, 0, 1));

            return 1;
        }

        return \file_put_contents($filename, $data, $flags, $context);
    }
}

namespace my127\Workspace\Tests\Test\GlobalService\Proxy {
    use my127\Workspace\GlobalService\Proxy\CertificateDownloader;
    use my127\Workspace\GlobalService\Proxy\CertificateDownloaderWriteFailure;
    use my127\Workspace\Tests\IntegrationTestCase;
    use Symfony\Component\Process\Process;

    class CertificateDownloaderTest extends IntegrationTestCase
    {
        private ?Process $server = null;
        private ?string $serverUrl = null;

        protected function tearDown(): void
        {
            CertificateDownloaderWriteFailure::$enabled = false;

            if ($this->server !== null) {
                $this->server->stop();
            }
        }

        public function testShortCertificateWritesDoNotReplaceExistingFiles(): void
        {
            $this->startCertificateServer();
            $this->workspace()->put('tls/my127.site.crt', 'existing certificate');
            $this->workspace()->put('tls/my127.site.key', 'existing key');

            CertificateDownloaderWriteFailure::$enabled = true;

            $this->expectException(\RuntimeException::class);
            $this->expectExceptionMessage('Could not download certificate sources for Proxy Domain "default"');

            try {
                (new CertificateDownloader())->download([
                    'default' => [
                        'name' => 'my127.site',
                        'https' => [
                            'crt' => $this->serverUrl . '/my127.site.crt',
                            'key' => $this->serverUrl . '/my127.site.key',
                        ],
                        'crt_file' => 'my127.site.crt',
                        'key_file' => 'my127.site.key',
                    ],
                ], $this->workspace()->path('tls'), 'proxy-domains.yml');
            } finally {
                self::assertSame('existing certificate', $this->workspace()->getContents('tls/my127.site.crt'));
                self::assertSame('existing key', $this->workspace()->getContents('tls/my127.site.key'));
                self::assertSame([], glob($this->workspace()->path('tls/proxy-domain-*')));
            }
        }

        private function startCertificateServer(): void
        {
            $this->workspace()->put('certs/my127.site.crt', str_repeat('new certificate', 100));
            $this->workspace()->put('certs/my127.site.key', str_repeat('new key', 100));

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
                if (@file_get_contents($this->serverUrl . '/my127.site.crt') !== false) {
                    return;
                }
                usleep(100000);
            }

            throw new \RuntimeException('Certificate fixture server did not start.');
        }
    }
}
