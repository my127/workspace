<?php

namespace Test\my127\Workspace\Types;

use Fixture;
use PHPUnit\Framework\TestCase;
use my127\Workspace\Tests\IntegrationTestCase;

class ConfdTest extends IntegrationTestCase
{
    /** @test */
    public function attributes_are_available_to_templates()
    {
        $path = $this->workspace()->loadSample('confd/attributes');
        $this->workspaceCommand('apply config');

        $this->assertEquals('Hello World', $this->workspace()->getContents('test.txt'));
    }

    /** @test */
    public function functions_are_available_to_templates()
    {
        $path = $this->workspace()->loadSample('confd/functions');
        $this->workspaceCommand('apply config');

        $this->assertEquals('6', $this->workspace()->getContents('test.txt'));
    }

    /** @test */
    public function template_src_is_suffixed_with_the_twig_extension()
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
    public function template_dst_when_not_specified_is_placed_in_the_same_location_as_src_without_the_twig_file_extension()
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
    public function whether_a_template_is_applied_or_not_can_be_controlled_with_a_conditional_expression()
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
    public function when_only_a_string_is_specified_it_is_treated_as_the_src_with_defaults_applied_to_the_other_attributes()
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
        $this->workspace()->put('workspace.yml', $workspace);
    }
}
