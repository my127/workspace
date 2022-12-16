<?php

namespace Test\my127\Console\Usage\Parser;

use my127\Console\Usage\Model\BooleanOptionValue;
use my127\Console\Usage\Model\OptionDefinition;
use my127\Console\Usage\Model\OptionDefinitionCollection;
use my127\Console\Usage\Model\StringOptionValue;
use PHPUnit\Framework\TestCase;

require_once __DIR__.'/../../../resources/usage.php';

class UsageParserTest extends TestCase
{
    /**
     * @test
     */
    public function an_argument_is_defined_as_an_alpha_numeric_string_between_angle_brackets_and_specified_as_an_alpha_numeric_string()
    {
        $result = usage('<environment>', 'development');

        $this->assertIsArray($result);
        $this->assertTrue(count($result) == 1);
    }

    /**
     * @test
     */
    public function an_argument_has_its_value_captured_and_provided()
    {
        $result = usage('<environment>', 'development');

        $this->assertIsArray($result);
        $this->assertTrue(count($result) == 1);
        $this->assertEquals("argument('environment', 'development')", $result[0]);
    }

    /**
     * @test
     */
    public function arguments_consume_the_first_available_token()
    {
        $result = usage('<environment> <task>', 'development deploy');

        $this->assertIsArray($result);
        $this->assertTrue(count($result) == 2);
        $this->assertEquals("argument('environment', 'development')", $result[0]);
        $this->assertEquals("argument('task', 'deploy')", $result[1]);
    }

    /**
     * @test
     */
    public function valid_when_argument_value_starts_with_alphanumeric_character()
    {
        $result = usage('<environment>', 'development');

        $this->assertIsArray($result);
        $this->assertTrue(count($result) == 1);
    }

    /**
     * @test
     */
    public function invalid_when_argument_value_does_not_start_with_an_alphanumeric_character()
    {
        $result = usage('<environment>', '-d');

        $this->assertIsBool($result);
        $this->assertFalse($result);
    }

    /**
     * @test
     */
    public function arguments_are_required_by_default()
    {
        $result = usage('<environment>', '');
        $this->assertFalse($result);
    }

    /**
     * @test
     */
    public function arguments_can_be_made_optional()
    {
        $result = usage('[<environment>]', '');

        $this->assertIsArray($result);
        $this->assertTrue(count($result) == 0);
    }

    /**
     * @test
     */
    public function a_command_is_denoted_by_an_alphanumerical_string()
    {
        $result = usage('command', 'command');

        $this->assertIsArray($result);
        $this->assertTrue(count($result) == 1);
        $this->assertEquals("command('command')", $result[0]);
    }

    /**
     * @test
     */
    public function a_command_by_default_is_required()
    {
        $result = usage('command', '');

        $this->assertIsBool($result);
        $this->assertEquals(false, $result);
    }

    /**
     * @test
     */
    public function valid_when_commands_specified_in_the_order_defined()
    {
        $result = usage('foo bar', 'foo bar');

        $this->assertIsArray($result);
        $this->assertTrue(count($result) == 2);
        $this->assertEquals("command('foo')", $result[0]);
        $this->assertEquals("command('bar')", $result[1]);
    }

    /**
     * @test
     */
    public function invalid_when_commands_specified_in_different_order_to_that_defined()
    {
        $result = usage('foo bar', 'bar foo');

        $this->assertIsBool($result);
        $this->assertFalse($result);
    }

    /**
     * @test
     */
    public function commands_are_required_by_default()
    {
        $result = usage('foo', '');
        $this->assertFalse($result);
    }

    /**
     * @test
     */
    public function commands_can_be_made_optional()
    {
        $result = usage('[foo]', '');
        $this->assertIsArray($result);
        $this->assertTrue(count($result) == 0);
    }

    /**
     * @test
     */
    public function a_single_dash_when_not_part_of_an_option_is_treated_as_command()
    {
        $result = usage('-', '-');
        $this->assertIsArray($result);
        $this->assertEquals(1, count($result));
        $this->assertEquals("command('-')", $result[0]);
    }

    /**
     * @test
     */
    public function short_option_is_treated_as_a_flag_with_a_true_value()
    {
        $result = usage('-h', '-h');

        $this->assertIsArray($result);
        $this->assertTrue(count($result) == 1);
        $this->assertEquals("option('h', 'true')", $result[0]);
    }

    /**
     * @test
     */
    public function options_are_orderless_and_provided_in_the_order_defined()
    {
        $result = usage('program -a -b -c command', 'program -a command -b -c');

        $this->assertIsArray($result);

        $this->assertEquals("option('a', 'true')", $result[1]);
        $this->assertEquals("option('b', 'true')", $result[2]);
        $this->assertEquals("option('c', 'true')", $result[3]);
    }

    /**
     * @test
     */
    public function short_options_can_be_described_as_a_short_sequence_and_specified_as_separate_options()
    {
        $result = usage('-abc', '-b -c -a');

        $this->assertIsArray($result);
        $this->assertEquals(3, count($result));

        $this->assertEquals("option('a', 'true')", $result[0]);
        $this->assertEquals("option('b', 'true')", $result[1]);
        $this->assertEquals("option('c', 'true')", $result[2]);
    }

    /**
     * @test
     */
    public function short_options_can_be_described_as_separate_options_and_specified_as_a_short_sequence()
    {
        $result = usage('-a -b -c', '-bca');

        $this->assertIsArray($result);
        $this->assertEquals(3, count($result));

        $this->assertEquals("option('a', 'true')", $result[0]);
        $this->assertEquals("option('b', 'true')", $result[1]);
        $this->assertEquals("option('c', 'true')", $result[2]);
    }

    /**
     * @test
     */
    public function short_options_can_be_described_and_specified_as_a_short_sequence()
    {
        $result = usage('-abc', '-bca');

        $this->assertIsArray($result);
        $this->assertEquals(3, count($result));

        $this->assertEquals("option('a', 'true')", $result[0]);
        $this->assertEquals("option('b', 'true')", $result[1]);
        $this->assertEquals("option('c', 'true')", $result[2]);
    }

    /**
     * @test
     */
    public function long_option_can_have_value_specified_after_space()
    {
        $result = usage('--environment=<env>', '--environment development');

        $this->assertIsArray($result);
        $this->assertEquals(1, count($result));
        $this->assertEquals("option('environment', 'development')", $result[0]);
    }

    /**
     * @test
     */
    public function long_option_can_have_value_specified_after_equals()
    {
        $result = usage('--environment=<env>', '--environment=development');

        $this->assertIsArray($result);
        $this->assertEquals(1, count($result));
        $this->assertEquals("option('environment', 'development')", $result[0]);
    }

    /**
     * @test
     */
    public function short_option_can_have_value_specified_without_space_or_equals()
    {
        $result = usage('-e=<env>', '-eDevelopment');

        $this->assertIsArray($result);
        $this->assertEquals(1, count($result));
        $this->assertEquals("option('e', 'Development')", $result[0]);
    }

    /**
     * @test
     */
    public function options_are_required_by_default()
    {
        $result = usage('-e=<env>', '');

        $this->assertIsBool($result);
        $this->assertFalse($result);
    }

    /**
     * @test
     */
    public function options_can_be_made_optional()
    {
        $result = usage('[-e=<env>]', '');

        $this->assertIsArray($result);
        $this->assertEquals(0, count($result));
    }

    /**
     * @test
     */
    public function option_definitions_can_all_be_added_at_once()
    {
        $optionRepository = new OptionDefinitionCollection();
        $optionRepository->add(new OptionDefinition(
            BooleanOptionValue::create(true),
            OptionDefinition::TYPE_BOOL,
            'h',
            'help'
        ));
        $optionRepository->add(new OptionDefinition(
            StringOptionValue::create('test'),
            OptionDefinition::TYPE_VALUE,
            'e',
            'environment'
        ));

        $result = usage('[options]', '-h --environment=development', $optionRepository);

        $this->assertIsArray($result);
        $this->assertEquals(2, count($result));
        $this->assertEquals("option('h', 'true')", $result[0]);
        $this->assertEquals("option('environment', 'development')", $result[1]);
    }

    /**
     * @test
     */
    public function passed_options_must_be_part_of_usage_definition()
    {
        $result = usage('[-hv] foo bar', '--unknown foo bar');
        $this->assertFalse($result);
    }
}