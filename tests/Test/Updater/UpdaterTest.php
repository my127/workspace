<?php

namespace my127\Workspace\Tests\Test\Updater;

use my127\Workspace\Updater\Exception\NoUpdateAvailableException;
use my127\Workspace\Updater\Exception\NoVersionDeterminedException;
use my127\Workspace\Updater\Updater;
use Phar;
use PHPUnit\Framework\TestCase;
use RuntimeException;

class UpdaterTest extends TestCase
{
    public static function tearDownAfterClass(): void
    {
        array_map('unlink', glob(__DIR__ . '/fixtures/generated/*.*'));
    }

    public static function setUpBeforeClass(): void
    {
        if (!is_dir(__DIR__ . '/fixtures/generated')) {
            mkdir(__DIR__ . '/fixtures/generated');
        }
    }

    /** @test */
    public function exception_thrown_when_error_fetching_releases()
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionCode(Updater::CODE_ERR_FETCHING_RELEASES);

        $updater = new Updater(__DIR__ . "/fixtures/foo.json", new NullOutput());
        $updater->update('1.0.0', '');
    }

    /** @test */
    public function exception_thrown_when_there_are_no_releases()
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionCode(Updater::CODE_NO_RELEASES);

        $updater = new Updater(__DIR__ . '/fixtures/empty-releases.json');
        $updater->update('1.0.0', '');
    }

    /** @test */
    public function exception_thrown_when_already_on_latest()
    {
        $this->prepareReleasesFixture('latest.json', '', '1.0.0');
        $this->expectException(NoUpdateAvailableException::class);

        $updater = new Updater(__DIR__ . '/fixtures/generated/latest.json', new NullOutput());
        $updater->update('1.0.0', '');
    }

    /** @test */
    public function exception_thrown_when_current_version_is_empty()
    {
        $this->prepareReleasesFixture('latest.json', '', '1.0.0');
        $this->expectException(NoVersionDeterminedException::class);

        $updater = new Updater(__DIR__ . '/fixtures/generated/latest.json', new NullOutput());
        $updater->update('', '');
    }

    /** @test */
    public function exception_thrown_when_on_more_recent_version()
    {
        $this->prepareReleasesFixture('older.json', '', '1.0.0');
        $this->expectException(NoUpdateAvailableException::class);

        $updater = new Updater(__DIR__ . '/fixtures/generated/older.json', new NullOutput());
        $updater->update('1.1.0', '');
    }

    /** @test */
    public function exception_thrown_when_next_release_cannot_be_downloaded()
    {
        $this->prepareReleasesFixture('invalid-release.json', __DIR__ . '/foo.baz.bar', '1.0.0');
        $this->expectException(RuntimeException::class);
        $this->expectExceptionCode(Updater::CODE_ERR_FETCHING_NEXT_RELEASE);

        $updater = new Updater(__DIR__ . '/fixtures/generated/invalid-release.json', new NullOutput());
        $updater->update('0.9.0', '');
    }

    /** @test */
    public function downloads_latest_release_to_desired_target_path()
    {
        $this->prepareFakePhar();
        $this->prepareReleasesFixture('valid.json', __DIR__ . '/fixtures/generated/fake.phar', '1.0.0');

        $temp = sys_get_temp_dir() . '/test-ws-download';
        $updater = new Updater(__DIR__ . '/fixtures/generated/valid.json', new NullOutput());
        $updater->update('0.9.0', $temp);

        $this->assertFileExists($temp);
    }

    private function prepareReleasesFixture(string $name, string $releasePath, string $version): void
    {
        $contents = file_get_contents(__DIR__ . '/fixtures/tpl/releases.json');
        $contents = str_replace(['%%browserDownloadUrl%%', '%%versionTag%%'], [$releasePath, $version], $contents);

        file_put_contents(__DIR__ . '/fixtures/generated/' . $name, $contents);
    }

    private function prepareFakePhar(): void
    {
        $phar = new Phar(__DIR__ . '/fixtures/generated/fake.phar');
        $phar->addFromString('foo', 'bar');
        $phar->setStub(Phar::createDefaultStub('foo.php', 'web/foo.php'));
        $phar->convertToExecutable(Phar::TAR, Phar::NONE);
        $phar->startBuffering();
        $phar->stopBuffering();
    }
}
