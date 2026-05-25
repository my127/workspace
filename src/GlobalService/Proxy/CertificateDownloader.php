<?php

namespace my127\Workspace\GlobalService\Proxy;

class CertificateDownloader
{
    public function download(array $domains, string $directory, string $registryPath): void
    {
        $domains = ProxyDomainConfiguration::normalizeDomains($domains);
        $directory = $this->resolveDirectory($directory);

        if (!is_dir($directory)) {
            mkdir($directory, 0755, true);
        }

        foreach ($domains as $id => $domain) {
            $this->downloadDomain($id, $domain, $directory, $registryPath);
        }
    }

    private function downloadDomain(string $id, array $domain, string $directory, string $registryPath): void
    {
        $temporaryFiles = [];

        try {
            $temporaryFiles['crt'] = $this->downloadToTemporaryFile($domain['https']['crt'], $directory);
            $temporaryFiles['key'] = $this->downloadToTemporaryFile($domain['https']['key'], $directory);

            rename($temporaryFiles['crt'], $directory . '/' . $domain['crt_file']);
            rename($temporaryFiles['key'], $directory . '/' . $domain['key_file']);
        } catch (\Throwable $e) {
            foreach ($temporaryFiles as $temporaryFile) {
                if (file_exists($temporaryFile)) {
                    unlink($temporaryFile);
                }
            }

            throw new \RuntimeException(sprintf(
                'Could not download certificate sources for Proxy Domain "%s". Check %s and verify the configured URLs are reachable. %s',
                $id,
                $registryPath,
                $e->getMessage()
            ), 0, $e);
        }
    }

    private function downloadToTemporaryFile(string $url, string $directory): string
    {
        $contents = @file_get_contents($url);
        if ($contents === false) {
            throw new \RuntimeException(sprintf('Failed source URL: %s.', $url));
        }

        $temporaryFile = tempnam($directory, 'proxy-domain-');
        if ($temporaryFile === false) {
            throw new \RuntimeException(sprintf('Could not create temporary file in %s.', $directory));
        }

        file_put_contents($temporaryFile, $contents);

        return $temporaryFile;
    }

    private function resolveDirectory(string $directory): string
    {
        if (str_starts_with($directory, '/')) {
            return $directory;
        }

        return getcwd() . '/' . $directory;
    }
}
