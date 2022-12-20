<?php

namespace spec\my127\Console\Usage\Parser;

use my127\Console\Factory\OptionValueFactory;
use my127\Console\Usage\Model\BooleanOptionValue;
use my127\Console\Usage\Model\OptionDefinition;
use my127\Console\Usage\Model\StringOptionValue;
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
    function let(OptionValueFactory $optionValueFactory)
    {
        $this->beConstructedWith($optionValueFactory);

        $optionValueFactory
            ->createFromType(OptionDefinition::TYPE_VALUE)
            ->willReturn(StringOptionValue::create('default-value'));
        $optionValueFactory
            ->createFromType(OptionDefinition::TYPE_BOOL)
            ->willReturn(BooleanOptionValue::create(false));
    }

    function it_returns_definition_when_short_option_is_provided()
    {
        $this->parse('-a')->shouldBeLike(new OptionDefinition(
            BooleanOptionValue::create(false),
            OptionDefinition::TYPE_BOOL,
            'a'
        ));
    }

    function it_returns_definition_when_long_option_is_provided()
    {
        $this->parse('--help')->shouldBeLike(new OptionDefinition(
            BooleanOptionValue::create(false),
            OptionDefinition::TYPE_BOOL,
            null,
            'help'
        ));
    }

    function it_ignores_leading_spaces_when_creating_option_definition()
    {
        $this->parse('     --help')->shouldBeLike(new OptionDefinition(
            BooleanOptionValue::create(false),
            OptionDefinition::TYPE_BOOL,
            null,
            'help'
        ));
    }

    function it_returns_definition_when_long_and_short_options_are_provided()
    {
        $expect = new OptionDefinition(
            BooleanOptionValue::create(false),
            OptionDefinition::TYPE_BOOL,
            'h',
            'help'
        );

        $this->parse('--help, -h')->shouldBeLike($expect);
        $this->parse('-h, --help')->shouldBeLike($expect);
        $this->parse('--help -h')->shouldBeLike($expect);
        $this->parse('-h --help')->shouldBeLike($expect);
    }

    function it_returns_definition_when_short_name_and_description_are_provided()
    {
        $this->parse('-h  An informative help message')
            ->shouldBeLike(new OptionDefinition(
                BooleanOptionValue::create(false),
                OptionDefinition::TYPE_BOOL,
                'h',
                null,
                'An informative help message'
            ));
    }

    function it_returns_definition_of_bool_type_when_no_argument_specified()
    {
        $this->parse('-h')->shouldBeLike(new OptionDefinition(
            BooleanOptionValue::create(false),
            OptionDefinition::TYPE_BOOL,
            'h'
        ));
    }

    function it_returns_definition_of_bool_type_and_false_default_when_no_argument_or_default_specified()
    {
        $this->parse('-h')->shouldBeLike(new OptionDefinition(
            BooleanOptionValue::create(false),
            OptionDefinition::TYPE_BOOL,
            'h'
        ));
    }

    function it_returns_definition_of_value_type_when_argument_is_specified()
    {
        $expect = new OptionDefinition(
            StringOptionValue::create('default-value'),
            OptionDefinition::TYPE_VALUE,
            'e',
            'environment',
            'Environment to which changes apply.',
            'ENVIRONMENT'
        );

        $this->parse('-e, --environment=<ENVIRONMENT>  Environment to which changes apply.')->shouldBeLike($expect);
        $this->parse('-e=<ENVIRONMENT>, --environment  Environment to which changes apply.')->shouldBeLike($expect);

        $this->parse('-e, --environment=ENVIRONMENT  Environment to which changes apply.')->shouldBeLike($expect);
        $this->parse('-e=ENVIRONMENT, --environment  Environment to which changes apply.')->shouldBeLike($expect);

        $this->parse('-e ENVIRONMENT --environment  Environment to which changes apply.')->shouldBeLike($expect);
        $this->parse('--environment -e ENVIRONMENT  Environment to which changes apply.')->shouldBeLike($expect);
    }

    function it_returns_definition_of_value_type_and_null_default_when_argument_specified_with_no_default()
    {
        $this->parse('-e ENV')->shouldBeLike(new OptionDefinition(
            StringOptionValue::create('default-value'),
            OptionDefinition::TYPE_VALUE,
            'e',
            null,
            null,
            'ENV'
        ));
    }

    function it_returns_definition_of_value_type_with_given_specified_default(OptionValueFactory $optionValueFactory)
    {
        $optionValueFactory
            ->createFromTypeAndValue(OptionDefinition::TYPE_VALUE, 'development')
            ->willReturn(StringOptionValue::create('development'));

        $this->parse('-e ENV  Environment to which changes apply [default: development]')
            ->shouldBeLike(
                new OptionDefinition(
                    StringOptionValue::create('development'),
                    OptionDefinition::TYPE_VALUE,
                    'e',
                    null,
                    'Environment to which changes apply [default: development]',
                    'ENV'
                )
            );
    }
}
