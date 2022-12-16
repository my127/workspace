<?php

namespace Test\my127\Workspace\Types;

use my127\Workspace\Tests\IntegrationTestCase;

class ConfdTest extends IntegrationTestCase
{
    /** @test */
    public function attributesAreAvailableToTemplates()
    {
        $this->workspace()->loadSample('confd/attributes');
        $this->workspaceCommand('apply config');

        $this->assertEquals('Hello World', $this->workspace()->getContents('test.txt'));
    }

    /** @test */
    public function functionsAreAvailableToTemplates()
    {
        $this->workspace()->loadSample('confd/functions');
        $this->workspaceCommand('apply config');

        $this->assertEquals('6', $this->workspace()->getContents('test.txt'));
    }

    /** @test */
    public function templateSrcIsSuffixedWithTheTwigExtension()
    {
        $path = $this->workspaceWithSampleData(<<<'EOD'
confd('workspace:/'):
  - { src: sample1.txt, dst: workspace:/sample1.txt }

command('apply config'): |
  #!php
  $ws->confd('workspace:/')->apply();
EOD
            , 'confd/simple');

        $this->workspaceCommand('apply config');

        $this->assertEquals($this->workspace()->getContents('sample1.txt.twig'), $this->workspace()->getContents('sample1.txt'));
    }

    /** @test */
    public function templateDstWhenNotSpecifiedIsPlacedInTheSameLocationAsSrcWithoutTheTwigFileExtension()
    {
        $path = $this->workspaceWithSampleData(<<<'EOD'
confd('workspace:/'):
  - { src: sample1.txt }

command('apply config'): |
  #!php
  $ws->confd('workspace:/')->apply();
EOD
            , 'confd/simple');

        $this->workspaceCommand('apply config');

        $this->assertEquals($this->workspace()->getContents('sample1.txt.twig'), $this->workspace()->getContents('sample1.txt'));
    }

    /** @test */
    public function whetherATemplateIsAppliedOrNotCanBeControlledWithAConditionalExpression()
    {
        $path = $this->workspaceWithSampleData(<<<'EOD'
confd('workspace:/'):
  - { src: sample1.txt, dst: workspace:/sample1.txt, when: true == true  }
  - { src: sample2.txt, dst: workspace:/sample2.txt, when: true == false }

command('apply config'): |
  #!php
  $ws->confd('workspace:/')->apply();
EOD
            , 'confd/simple');

        $this->workspaceCommand('apply config');

        $this->assertTrue($this->workspace()->exists('sample1.txt'));
        $this->assertFalse($this->workspace()->exists('sample2.txt'));
    }

    /** @test */
    public function whenOnlyAStringIsSpecifiedItIsTreatedAsTheSrcWithDefaultsAppliedToTheOtherAttributes()
    {
        $this->workspaceWithSampleData(<<<'EOD'
confd('workspace:/'):
  - sample1.txt

command('apply config'): |
  #!php
  $ws->confd('workspace:/')->apply();
EOD
            , 'confd/simple');

        $this->workspaceCommand('apply config');

        $this->assertEquals(
            $this->workspace()->getContents('sample1.txt.twig'),
            $this->workspace()->getContents('sample1.txt'),
        );
    }

    private function workspaceWithSampleData(string $workspace, string $sampleName)
    {
        $this->workspace()->loadSample($sampleName);
        $this->createWorkspaceYml($workspace);
    }
}
