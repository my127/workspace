<?php

namespace spec\my127\Console\Usage\Parser;

use my127\Console\Usage\Model\OptionDefinition;
use PhpSpec\ObjectBehavior;
use PhpSpec\Wrapper\Subject;
use Prophecy\Argument;

/**
 * Class OptionDefinitionParserSpec
 *
 * @method Subject parse(string $option)
 */
class OptionDefinitionParserSpec extends ObjectBehavior
{
    function it_returns_definition_when_short_option_is_provided()
    {
        $this->parse('-a')->shouldBeLike(new OptionDefinition('a'));
    }

    function it_returns_definition_when_long_option_is_provided()
    {
        $this->parse('--help')->shouldBeLike(new OptionDefinition(null, 'help'));
    }

    function it_ignores_leading_spaces_when_creating_option_definition()
    {
        $this->parse('     --help')->shouldBeLike(new OptionDefinition(null, 'help'));
    }

    function it_returns_definition_when_long_and_short_options_are_provided()
    {
        $expect = new OptionDefinition('h', 'help');

        $this->parse('--help, -h')->shouldBeLike($expect);
        $this->parse('-h, --help')->shouldBeLike($expect);
        $this->parse('--help -h')->shouldBeLike($expect);
        $this->parse('-h --help')->shouldBeLike($expect);
    }

    function it_returns_definition_when_short_name_and_description_are_provided()
    {
        $this->parse('-h  An informative help message')
            ->shouldBeLike(new OptionDefinition('h', null, 'An informative help message'));
    }

    function it_returns_definition_of_bool_type_when_no_argument_specified()
    {
        $this->parse('-h')->shouldBeLike(new OptionDefinition('h', null, null, OptionDefinition::TYPE_BOOL));
    }

    function it_returns_definition_of_bool_type_and_false_default_when_no_argument_or_default_specified()
    {
        $this->parse('-h')->shouldBeLike(new OptionDefinition('h', null, null, OptionDefinition::TYPE_BOOL, false));
    }

    function it_returns_definition_of_value_type_when_argument_is_specified()
    {
        $expect = new OptionDefinition('e', 'environment', 'Environment to which changes apply.', OptionDefinition::TYPE_VALUE, null, 'ENVIRONMENT');

        $this->parse('-e, --environment=<ENVIRONMENT>  Environment to which changes apply.')->shouldBeLike($expect);
        $this->parse('-e=<ENVIRONMENT>, --environment  Environment to which changes apply.')->shouldBeLike($expect);

        $this->parse('-e, --environment=ENVIRONMENT  Environment to which changes apply.')->shouldBeLike($expect);
        $this->parse('-e=ENVIRONMENT, --environment  Environment to which changes apply.')->shouldBeLike($expect);

        $this->parse('-e ENVIRONMENT --environment  Environment to which changes apply.')->shouldBeLike($expect);
        $this->parse('--environment -e ENVIRONMENT  Environment to which changes apply.')->shouldBeLike($expect);
    }

    function it_returns_definition_of_value_type_and_null_default_when_argument_specified_with_no_default()
    {
        $this->parse('-e ENV')->shouldBeLike(new OptionDefinition('e', null, null, OptionDefinition::TYPE_VALUE, null, 'ENV'));
    }

    function it_returns_definition_of_value_type_with_given_specified_default()
    {
        $this->parse('-e ENV  Environment to which changes apply [default: development]')
            ->shouldBeLike(new OptionDefinition(
                'e',
                null,
                'Environment to which changes apply [default: development]',
                OptionDefinition::TYPE_VALUE,
                'development',
                'ENV'
            ));
    }
}
