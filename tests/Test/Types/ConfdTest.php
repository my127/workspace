<?php

namespace Test\my127\Workspace\Types;

use Fixture;
use PHPUnit\Framework\TestCase;

class ConfdTest extends TestCase
{
    /** @test */
    public function attributes_are_available_to_templates()
    {
        $path = Fixture::sampleData('confd/attributes');
        run('apply config');

        $this->assertEquals('Hello World', file_get_contents($path.'/test.txt'));
    }

    /** @test */
    public function functions_are_available_to_templates()
    {
        $path = Fixture::sampleData('confd/functions');
        run('apply config');

        $this->assertEquals('6', file_get_contents($path.'/test.txt'));
    }

    /** @test */
    public function template_src_is_suffixed_with_the_twig_extension()
    {
        $path = Fixture::workspaceWithSampleData(<<<'EOD'
confd('workspace:/'):
  - { src: sample1.txt, dst: workspace:/sample1.txt }

command('apply config'): |
  #!php
  $ws->confd('workspace:/')->apply();
EOD
            , 'confd/simple');

        run('apply config');

        $this->assertEquals(file_get_contents($path.'/sample1.txt.twig'), file_get_contents($path.'/sample1.txt'));
    }

    /** @test */
    public function template_dst_when_not_specified_is_placed_in_the_same_location_as_src_without_the_twig_file_extension()
    {
        $path = Fixture::workspaceWithSampleData(<<<'EOD'
confd('workspace:/'):
  - { src: sample1.txt }

command('apply config'): |
  #!php
  $ws->confd('workspace:/')->apply();
EOD
            , 'confd/simple');

        run('apply config');

        $this->assertEquals(file_get_contents($path.'/sample1.txt.twig'), file_get_contents($path.'/sample1.txt'));
    }

    /** @test */
    public function whether_a_template_is_applied_or_not_can_be_controlled_with_a_conditional_expression()
    {
        $path = Fixture::workspaceWithSampleData(<<<'EOD'
confd('workspace:/'):
  - { src: sample1.txt, dst: workspace:/sample1.txt, when: true == true  }
  - { src: sample2.txt, dst: workspace:/sample2.txt, when: true == false }

command('apply config'): |
  #!php
  $ws->confd('workspace:/')->apply();
EOD
            , 'confd/simple');

        run('apply config');

        $this->assertTrue(file_exists($path.'/sample1.txt'));
        $this->assertFalse(file_exists($path.'/sample2.txt'));
    }

    /** @test */
    public function when_only_a_string_is_specified_it_is_treated_as_the_src_with_defaults_applied_to_the_other_attributes()
    {
        $path = Fixture::workspaceWithSampleData(<<<'EOD'
confd('workspace:/'):
  - sample1.txt

command('apply config'): |
  #!php
  $ws->confd('workspace:/')->apply();
EOD
        , 'confd/simple');

        run('apply config');

        $this->assertEquals(file_get_contents($path.'/sample1.txt.twig'), file_get_contents($path.'/sample1.txt'));
    }
}
