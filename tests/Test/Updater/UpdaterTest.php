<?php

namespace my127\Workspace\Tests\Test\Updater;

use my127\Workspace\Updater\Exception\NoUpdateAvailableException;
use my127\Workspace\Updater\Exception\NoVersionDeterminedException;
use my127\Workspace\Updater\Updater;
use PHPUnit\Framework\TestCase;

class UpdaterTest extends TestCase
{
    public static function tearDownAfterClass(): void
    {
        $files = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator(__DIR__ . '/fixtures/generated', \RecursiveDirectoryIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::CHILD_FIRST
        );
        foreach ($files as $file) {
            if ($file->isDir()) {
                rmdir($file);
            } else {
                unlink($file);
            }
        }
    }

    protected function tearDown(): void
    {
        if (file_exists(__DIR__ . '/fixtures/generated/fake.phar')) {
            \Phar::unlinkArchive(__DIR__ . '/fixtures/generated/fake.phar');
        }
        if (file_exists(__DIR__ . '/fixtures/generated/fake.phar.tar')) {
            \Phar::unlinkArchive(__DIR__ . '/fixtures/generated/fake.phar.tar');
        }
    }

    public static function setUpBeforeClass(): void
    {
        if (!is_dir(__DIR__ . '/fixtures/generated')) {
            mkdir(__DIR__ . '/fixtures/generated');
        }
    }

    /** @test */
    public function exceptionThrownWhenErrorFetchingReleases()
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionCode(Updater::CODE_ERR_FETCHING_RELEASES);

        $updater = new Updater(__DIR__ . '/fixtures/foo.json', new NullOutput());
        $updater->updateLatest('1.0.0', '');
    }

    /** @test */
    public function exceptionThrownWhenAlreadyOnLatest()
    {
        $this->prepareLatestFixture('valid/latest', '', '1.0.0');
        $this->expectException(NoUpdateAvailableException::class);

        $updater = new Updater(__DIR__ . '/fixtures/generated/valid', new NullOutput());
        $updater->updateLatest('1.0.0', '');
    }

    /** @test */
    public function exceptionThrownWhenCurrentVersionIsEmpty()
    {
        $this->prepareLatestFixture('valid/latest', '', '1.0.0');
        $this->expectException(NoVersionDeterminedException::class);

        $updater = new Updater(__DIR__ . '/fixtures/generated/valid', new NullOutput());
        $updater->updateLatest('', '');
    }

    /** @test */
    public function exceptionThrownWhenOnMoreRecentVersion()
    {
        $this->prepareLatestFixture('older/latest', '', '1.0.0');
        $this->expectException(NoUpdateAvailableException::class);

        $updater = new Updater(__DIR__ . '/fixtures/generated/older', new NullOutput());
        $updater->updateLatest('1.1.0', '');
    }

    /** @test */
    public function exceptionThrownWhenNextReleaseCannotBeDownloaded()
    {
        $this->prepareLatestFixture('invalid-release/latest', __DIR__ . '/foo.baz.bar', '1.0.0');
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionCode(Updater::CODE_ERR_FETCHING_NEXT_RELEASE);

        $updater = new Updater(__DIR__ . '/fixtures/generated/invalid-release', new NullOutput());
        $updater->updateLatest('0.9.0', '');
    }

    /** @test */
    public function downloadsLatestReleaseToDesiredTargetPath()
    {
        $this->prepareFakePhar();
        $this->prepareLatestFixture('valid/latest', __DIR__ . '/fixtures/generated/fake.phar', '1.0.0');

        $temp = sys_get_temp_dir() . '/test-ws-download';
        $updater = new Updater(__DIR__ . '/fixtures/generated/valid', new NullOutput());
        $updater->updateLatest('0.9.0', $temp);

        $this->assertFileExists($temp);
    }

    /** @test */
    public function downloadsLatestCandidateOfConstraintToDesiredTargetPath()
    {
        $this->prepareFakePhar();
        $this->prepareReleasesFixture('valid.json', __DIR__ . '/fixtures/generated/fake.phar', '1.0.0', '0.9.1');

        $temp = sys_get_temp_dir() . '/test-ws-download';
        $updater = new Updater(__DIR__ . '/fixtures/generated/valid.json', new NullOutput());
        $updater->update('0.9.0', '~0.9.1', $temp);

        $this->assertFileExists($temp);
    }

    /** @test */
    public function downloadsLatestStableCandidateOfConstraintToDesiredTargetPath()
    {
        $this->prepareFakePhar();
        $this->prepareReleasesFixture('valid.json', __DIR__ . '/fixtures/generated/fake.phar', '1.0.0-alpha1', '0.9.1');

        $temp = sys_get_temp_dir() . '/test-ws-download';
        $updater = new Updater(__DIR__ . '/fixtures/generated/valid.json', new NullOutput());
        $updater->update('0.9.0', '~0.9.1', $temp);

        $this->assertFileExists($temp);
    }

    /** @test */
    public function exceptionThrownWhenNoStableCandidate()
    {
        $this->prepareFakePhar();
        $this->prepareReleasesFixture('valid.json', __DIR__ . '/fixtures/generated/fake.phar', '1.0.0-alpha1', '0.9.1-alpha1');
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionCode(Updater::CODE_ERR_FETCHING_RELEASES);

        $temp = sys_get_temp_dir() . '/test-ws-download';
        $updater = new Updater(__DIR__ . '/fixtures/generated/valid.json', new NullOutput());
        $updater->update('0.9.0', '~0.9.1', $temp);
    }

    /** @test */
    public function exceptionThrownWhenNoRCCandidate()
    {
        $this->prepareFakePhar();
        $this->prepareReleasesFixture('valid.json', __DIR__ . '/fixtures/generated/fake.phar', '1.0.0-alpha1', '0.9.1-alpha1');
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionCode(Updater::CODE_ERR_FETCHING_RELEASES);

        $temp = sys_get_temp_dir() . '/test-ws-download';
        $updater = new Updater(__DIR__ . '/fixtures/generated/valid.json', new NullOutput());
        $updater->update('0.9.0', '~0.9.1@RC', $temp);
    }

    /** @test */
    public function downloadsLatestAlphaCandidateOfConstraintToDesiredTargetPath()
    {
        $this->prepareFakePhar();
        $this->prepareReleasesFixture('valid.json', __DIR__ . '/fixtures/generated/fake.phar', '1.0.0-alpha1', '0.9.1-alpha1');

        $temp = sys_get_temp_dir() . '/test-ws-download';
        $updater = new Updater(__DIR__ . '/fixtures/generated/valid.json', new NullOutput());
        $updater->update('0.9.0', '~0.9.0@alpha', $temp);

        $this->assertFileExists($temp);
    }

    private function prepareReleasesFixture(string $name, string $releasePath, string $version1, string $version2): void
    {
        $contents = file_get_contents(__DIR__ . '/fixtures/tpl/releases.json');
        $contents = str_replace(['%%browserDownloadUrl%%', '%%versionTag1%%', '%%versionTag2%%'], [$releasePath, $version1, $version2], $contents);

        file_put_contents(__DIR__ . '/fixtures/generated/' . $name, $contents);
    }

    private function prepareLatestFixture(string $name, string $releasePath, string $version): void
    {
        $dir = dirname(__DIR__ . '/fixtures/generated/' . $name);
        if (!file_exists($dir)) {
            mkdir($dir);
        }
        $contents = file_get_contents(__DIR__ . '/fixtures/tpl/latest.json');
        $contents = str_replace(['%%browserDownloadUrl%%', '%%versionTag%%'], [$releasePath, $version], $contents);

        file_put_contents(__DIR__ . '/fixtures/generated/' . $name, $contents);
    }

    private function prepareFakePhar(): void
    {
        $phar = new \Phar(__DIR__ . '/fixtures/generated/fake.phar');
        $phar->addFromString('foo', 'bar');
        $phar->setStub(\Phar::createDefaultStub('foo.php', 'web/foo.php'));
        $phar->convertToExecutable(\Phar::TAR, \Phar::NONE);
        $phar->startBuffering();
        $phar->stopBuffering();
    }
}
