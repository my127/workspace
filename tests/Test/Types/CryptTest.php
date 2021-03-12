<?php

namespace Test\my127\Workspace\Types;

use Fixture;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Process\Process;
use my127\Workspace\Tests\IntegrationTestCase;

class CryptTest extends IntegrationTestCase
{
    /** @test */
    public function secrets_can_be_encrypted_and_decrypted_given_a_key()
    {
        $this->workspace()->put('workspace.yml', <<<'EOD'
key('default'): 81a7fa14a8ceb8e1c8860031e2bac03f4b939de44fa1a78987a3fcff1bf57100
EOD
        );

        $encrypted = trim($this->ws('secret encrypt "Hello World"')->getOutput());
        $decrypted = trim($this->ws('secret decrypt "'.$encrypted.'"')->getOutput());

        $this->assertTrue($encrypted != "Hello World");
        $this->assertTrue($decrypted == "Hello World");
    }

    /** @test */
    public function secrets_as_part_of_an_expression_can_be_decrypted()
    {
        $this->workspace()->put('workspace.yml', <<<'EOD'

key('default'): 81a7fa14a8ceb8e1c8860031e2bac03f4b939de44fa1a78987a3fcff1bf57100

attribute('message'): = decrypt('YTozOntpOjA7czo3OiJkZWZhdWx0IjtpOjE7czoyNDoi98rFejkefPnZG1CjzGeFyvSAMgafKv2TIjtpOjI7czoyNzoiSwcG2YiM3vV8CdZXgxDM2q+ZmRmPRNyz7OgcIjt9')

command('hello'): |
  #!bash|@
  echo "@('message')"

EOD
        );

        $this->assertEquals("Hello World", trim($this->ws('hello')->getOutput()));
    }

    /** @test */
    function default_key_can_be_specified_as_an_environment_variable()
    {
        $this->workspace()->put('workspace.yml', <<<'EOD'

attribute('message'): = decrypt('YTozOntpOjA7czo3OiJkZWZhdWx0IjtpOjE7czoyNDoi98rFejkefPnZG1CjzGeFyvSAMgafKv2TIjtpOjI7czoyNzoiSwcG2YiM3vV8CdZXgxDM2q+ZmRmPRNyz7OgcIjt9')

command('hello'): |
  #!bash|@
  echo "@('message')"

EOD
        );

        $this->assertEquals("Hello World", trim($this->ws('hello', '/', [
            'MY127WS_KEY' => '81a7fa14a8ceb8e1c8860031e2bac03f4b939de44fa1a78987a3fcff1bf57100',
        ])->getOutput()));
    }
}
