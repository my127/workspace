<?php

namespace Test\my127\Workspace\Types;

use Fixture;
use PHPUnit\Framework\TestCase;

class CryptTest extends TestCase
{
    /** @test */
    public function secrets_can_be_encrypted_and_decrypted_given_a_key()
    {
        Fixture::workspace(<<<'EOD'
key('default'): 81a7fa14a8ceb8e1c8860031e2bac03f4b939de44fa1a78987a3fcff1bf57100
EOD
        );

        $encrypted = trim(run('secret encrypt "Hello World"'));
        $decrypted = trim(run('secret decrypt "'.$encrypted.'"'));

        $this->assertTrue($encrypted != "Hello World");
        $this->assertTrue($decrypted == "Hello World");
    }

    /** @test */
    public function secrets_as_part_of_an_expression_can_be_decrypted()
    {
        Fixture::workspace(<<<'EOD'

key('default'): 81a7fa14a8ceb8e1c8860031e2bac03f4b939de44fa1a78987a3fcff1bf57100

attribute('message'): = decrypt('YTozOntpOjA7czo3OiJkZWZhdWx0IjtpOjE7czoyNDoi98rFejkefPnZG1CjzGeFyvSAMgafKv2TIjtpOjI7czoyNzoiSwcG2YiM3vV8CdZXgxDM2q+ZmRmPRNyz7OgcIjt9')

command('hello'): |
  #!bash|@
  echo "@('message')"

EOD
        );

        $this->assertEquals("Hello World", trim(run('hello')));
    }
}
