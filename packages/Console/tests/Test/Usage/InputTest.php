<?php

namespace Test\my127\Console\Usage;

use my127\Console\Factory\OptionValueFactory;
use my127\Console\Usage\Input;
use my127\Console\Usage\Model\Argument;
use my127\Console\Usage\Model\BooleanOptionValue;
use my127\Console\Usage\Model\Command;
use my127\Console\Usage\Model\Option;
use my127\Console\Usage\Model\OptionDefinition;
use my127\Console\Usage\Model\OptionDefinitionCollection;
use my127\Console\Usage\Model\StringOptionValue;
use PHPUnit\Framework\Assert;
use PHPUnit\Framework\TestCase;

class InputTest extends TestCase
{

    public function test_input_representation()
    {
        $input = new Input([
            new Command('foo-cmd'),
            new Argument('test-arg', 123),
            new Option(
                'bool-option',
                new OptionDefinition(BooleanOptionValue::create(false), OptionDefinition::TYPE_BOOL, 'b'),
                true
            ),
            new Option(
                'text-option',
                new OptionDefinition(StringOptionValue::create('default-value'), OptionDefinition::TYPE_VALUE, 't'),
                'actual-value'
            ),
        ], new OptionDefinitionCollection(), new OptionValueFactory());

        Assert::assertEquals(['foo-cmd'], $input->getCommand());
        Assert::assertEquals(123, $input->getArgument('test-arg'));
        Assert::assertEquals(BooleanOptionValue::create(true), $input->getOption('b'));
        Assert::assertEquals(StringOptionValue::create('actual-value'), $input->getOption('t'));
    }

    public function test_input_representation_with_short_getters()
    {
        $input = new Input([
            new Command('foo-cmd'),
            new Argument('test-arg', 123),
            new Option(
                'bool-option',
                new OptionDefinition(BooleanOptionValue::create(false), OptionDefinition::TYPE_BOOL, 'b'),
                true
            ),
            new Option(
                'text-option',
                new OptionDefinition(StringOptionValue::create('default-value'), OptionDefinition::TYPE_VALUE, 't'),
                'actual-value'
            ),
        ], new OptionDefinitionCollection(), new OptionValueFactory());

        Assert::assertEquals(['foo-cmd'], $input->getCommand());
        Assert::assertEquals(123, $input->argument('test-arg'));
        Assert::assertEquals(true, $input->option('b'));
        Assert::assertEquals('actual-value', $input->option('t'));
    }

    public function test_default_options()
    {
        $optionRepository = new OptionDefinitionCollection();
        $optionRepository->add(
            new OptionDefinition(BooleanOptionValue::create(true), OptionDefinition::TYPE_BOOL, 'b0')
        );
        $optionRepository->add(
            new OptionDefinition(StringOptionValue::create('other-default-value'), OptionDefinition::TYPE_BOOL, 't0')
        );

        $input = new Input([
            new Option(
                'bool-option',
                new OptionDefinition(BooleanOptionValue::create(false), OptionDefinition::TYPE_BOOL, 'b')
            ),
            new Option(
                'text-option',
                new OptionDefinition(StringOptionValue::create('default-value'), OptionDefinition::TYPE_VALUE, 't')
            ),
        ], $optionRepository, new OptionValueFactory());

        Assert::assertEquals(BooleanOptionValue::create(false), $input->getOption('b'));
        Assert::assertEquals(StringOptionValue::create('default-value'), $input->getOption('t'));
        Assert::assertEquals(BooleanOptionValue::create(true), $input->getOption('b0'));
        Assert::assertEquals(StringOptionValue::create('other-default-value'), $input->getOption('t0'));
    }

    public function test_default_options_with_short_getters()
    {
        $optionRepository = new OptionDefinitionCollection();
        $optionRepository->add(
            new OptionDefinition(BooleanOptionValue::create(true), OptionDefinition::TYPE_BOOL, 'b0')
        );
        $optionRepository->add(
            new OptionDefinition(StringOptionValue::create('other-default-value'), OptionDefinition::TYPE_BOOL, 't0')
        );

        $input = new Input([
            new Option(
                'bool-option',
                new OptionDefinition(BooleanOptionValue::create(false), OptionDefinition::TYPE_BOOL, 'b')
            ),
            new Option(
                'text-option',
                new OptionDefinition(StringOptionValue::create('default-value'), OptionDefinition::TYPE_VALUE, 't')
            ),
        ], $optionRepository, new OptionValueFactory());

        Assert::assertEquals(false, $input->option('b'));
        Assert::assertEquals('default-value', $input->option('t'));
        Assert::assertEquals(true, $input->option('b0'));
        Assert::assertEquals('other-default-value', $input->option('t0'));
    }
}
