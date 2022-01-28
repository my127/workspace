<?php

namespace Test\my127\Workspace\Types;

use my127\Workspace\Tests\IntegrationTestCase;

class CryptTest extends IntegrationTestCase
{
    /** @test */
    public function secretsCanBeEncryptedAndDecryptedGivenAKey()
    {
        $this->createWorkspaceYml(<<<'EOD'
key('default'): 81a7fa14a8ceb8e1c8860031e2bac03f4b939de44fa1a78987a3fcff1bf57100
EOD
        );

        $encrypted = trim($this->workspaceCommand('secret encrypt "Hello World"')->getOutput());
        $decrypted = trim($this->workspaceCommand('secret decrypt "' . $encrypted . '"')->getOutput());

        $this->assertTrue($encrypted != 'Hello World');
        $this->assertTrue($decrypted == 'Hello World');
    }

    /** @test */
    public function secretsAsPartOfAnExpressionCanBeDecrypted()
    {
        $this->createWorkspaceYml(<<<'EOD'

key('default'): 81a7fa14a8ceb8e1c8860031e2bac03f4b939de44fa1a78987a3fcff1bf57100

attribute('message'): = decrypt('YTozOntpOjA7czo3OiJkZWZhdWx0IjtpOjE7czoyNDoi98rFejkefPnZG1CjzGeFyvSAMgafKv2TIjtpOjI7czoyNzoiSwcG2YiM3vV8CdZXgxDM2q+ZmRmPRNyz7OgcIjt9')

command('hello'): |
  #!bash|@
  echo "@('message')"

EOD
        );

        $this->assertEquals('Hello World', trim($this->workspaceCommand('hello')->getOutput()));
    }

    /** @test */
    public function defaultKeyCanBeSpecifiedAsAnEnvironmentVariable()
    {
        $this->createWorkspaceYml(<<<'EOD'

attribute('message'): = decrypt('YTozOntpOjA7czo3OiJkZWZhdWx0IjtpOjE7czoyNDoi98rFejkefPnZG1CjzGeFyvSAMgafKv2TIjtpOjI7czoyNzoiSwcG2YiM3vV8CdZXgxDM2q+ZmRmPRNyz7OgcIjt9')

command('hello'): |
  #!bash|@
  echo "@('message')"

EOD
        );

        $this->assertEquals('Hello World', trim($this->workspaceCommand('hello', '/', [
            'MY127WS_KEY' => '81a7fa14a8ceb8e1c8860031e2bac03f4b939de44fa1a78987a3fcff1bf57100',
        ])->getOutput()));
    }

    /** @test */
    public function secretFilesCanEncryptedAndDecryptedGivenAKey()
    {
        $this->createWorkspaceYml(<<<'EOD'
key('default'): 81a7fa14a8ceb8e1c8860031e2bac03f4b939de44fa1a78987a3fcff1bf57100
EOD
        );

        $contents = $this->workspace()->getContents('workspace.yml');
        $encrypted = trim($this->workspaceCommand('secret encrypt-file "workspace.yml"')->getOutput());
        $decrypted = trim($this->workspaceCommand('secret decrypt "' . $encrypted . '"')->getOutput());

        $this->assertTrue($encrypted != $contents);
        $this->assertTrue($decrypted == $contents);
    }
}
