<?php

namespace Test\my127\Workspace\Types;

use my127\Workspace\Expression\Expression;
use my127\Workspace\Path\Paths\CWD;
use my127\Workspace\Tests\IntegrationTestCase;
use my127\Workspace\Types\Attribute\Collection as AttributeCollection;

class AttributeTest extends IntegrationTestCase
{
    /** @test */
    public function normalAttributeKeyCanBeSetAndRetrieved()
    {
        $this->createWorkspaceYml(<<<'EOD'
attribute('message'): Hello World

command('speak'): |
  #!bash|@
  echo -n "@('message')"
EOD
        );

        $this->assertEquals('Hello World', $this->workspaceCommand('speak')->getOutput());
    }

    /** @test */
    public function normalAttributeRootObjectCanBeSetAndRetrieved()
    {
        $this->createWorkspaceYml(<<<'EOD'
attributes:
  my:
    message: Hello World

command('speak'): |
  #!bash|@
  echo -n "@('my.message')"
EOD
        );

        $this->assertEquals('Hello World', $this->workspaceCommand('speak')->getOutput());
    }

    /** @test */
    public function attributeValueCanBeAnExpression()
    {
        $this->createWorkspaceYml(<<<'EOD'
attribute('db'):
  driver: mysql
  host: localhost
  name: application
  
attribute('db.dsn'): = @('db.driver') ~ ':host=' ~ @('db.host') ~ ';dbname=' ~ @('db.name')

command('speak'): |
  #!bash|@
  echo -n "@('db.dsn')"
EOD
        );

        $this->assertEquals('mysql:host=localhost;dbname=application', $this->workspaceCommand('speak')->getOutput());
    }

    /** @test */
    public function issetReturnsFalseWhenAttributeIsNotDefined()
    {
        $this->createWorkspaceYml(<<<'EOD'
command('isset'): |
  #!php
  echo (isset($ws['message'])) ? 'yes' : 'no';
EOD
        );

        $this->assertEquals('no', $this->workspaceCommand('isset')->getOutput());
    }

    /** @test */
    public function issetReturnsTrueWhenAttributeIsDefinedAndHasAValue()
    {
        $this->createWorkspaceYml(<<<'EOD'
attribute('message'): Hello World
command('isset'): |
  #!php
  echo (isset($ws['message'])) ? 'yes' : 'no';
EOD
        );

        $this->assertEquals('yes', $this->workspaceCommand('isset')->getOutput());
    }

    /** @test */
    public function issetReturnsTrueEvenWhenAttributeValueIsNull()
    {
        $this->createWorkspaceYml(<<<'EOD'
attribute('message'): null
command('isset'): |
  #!php
  echo (isset($ws['message'])) ? 'yes' : 'no';
EOD
        );

        $this->assertEquals('yes', $this->workspaceCommand('isset')->getOutput());
    }

    /** @test */
    public function nullValuesAreAlsoRepresentedInternallyAsNull()
    {
        $this->createWorkspaceYml(<<<'EOD'
attribute('message'): null
command('isnull'): |
  #!php
  echo (is_null($ws['message'])) ? 'yes' : 'no';
EOD
        );

        $this->assertEquals('yes', $this->workspaceCommand('isnull')->getOutput());
    }

    /**
     * @test
     *
     * @dataProvider provide_attribute_precedence_is_respected
     */
    public function attributePrecedenceIsRespected(string $attribute, string $expected)
    {
        $this->workspace()->loadSample('attribute/precedence');
        $this->assertEquals($expected, $this->workspaceCommand(sprintf(
            'get "%s"',
            $attribute
        ), 'workspace')->getOutput());
    }

    public function provide_attribute_precedence_is_respected(): \Generator
    {
        yield ['key.1', 'Hello From harness.default'];
        yield ['key.2', 'Hello From harness.normal'];
        yield ['key.3', 'Hello From harness.override'];
        yield ['key.4', 'Hello From harness.override'];
        yield ['key.5', 'Hello From harness.override'];
        yield ['key.6', 'Hello From workspace.override'];
        yield ['key.7', 'Hello From workspace.override'];
        yield ['key.8', 'Hello From workspace.override'];
        yield ['key.9', 'Hello From global.override'];
    }

    /** @test */
    public function attributeMetadataSourcesAreCorrectAndOrdered()
    {
        $attributes = new AttributeCollection(new Expression(new CWD()));

        $attrData0 = [
          'level1' => [
            'level2a' => [
              'level3a' => 'val3a',
              'level3b' => 'val3b',
              'level3c' => 'val3c',
            ],
            'level2b' => [
              'level3d' => 'val3d',
            ],
          ],
        ];

        $attrData1 = [
          'level1' => [
            'level2a' => [
              'level3a' => 'val3a',
              'level3b' => 'val3b',
              'level3c' => 'val3c',
            ],
            'level2b' => [
              'level3d' => 'val3d',
              'level3e' => [
                'level4a' => 'val4a',
                'level4b' => 'val4b',
              ],
            ],
          ],
        ];

        $attrData2 = [
          'level1' => [
            'level2b' => [
              'level3e' => [
                'level4a' => 'val4a-override',
              ],
            ],
          ],
        ];

        $attributes->add($attrData0, 'attrData0 array', 1);
        $attributes->add($attrData2, 'attrData2 array', 5);
        $attributes->add($attrData1, 'attrData1 array', 1);

        $metadata = $attributes->getAttributeMetadata('level1.level2b.level3e.level4a');
        $this->assertNotNull($metadata);

        $sources = $metadata['source'];
        $this->assertEquals(2, count($sources));
        $this->assertEquals('attrData2 array', array_pop($sources));
        $this->assertEquals('attrData1 array', array_pop($sources));
    }

    /** @test */
    public function duplicateAttributeDefinitionDoesNotCauseErrorInConfigDump()
    {
        $this->createWorkspaceYml(<<<'EOD'
attribute('message'): Hello World

attributes:
    message: Olleh Dlrow
EOD
        );

        $this->assertFalse(strpos($this->workspaceCommand('config dump --key=message')->getOutput(), 'World'));
    }
}
