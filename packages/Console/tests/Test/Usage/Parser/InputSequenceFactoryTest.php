<?php

namespace Test\my127\Console\Usage\Parser;

use my127\Console\Factory\OptionValueFactory;
use my127\Console\Usage\Model\OptionDefinition;
use my127\Console\Usage\Model\OptionDefinitionCollection;
use my127\Console\Usage\Parser\InputSequence;
use my127\Console\Usage\Parser\InputSequenceFactory;
use PHPUnit\Framework\TestCase;

class InputSequenceFactoryTest extends TestCase
{
    /**
     * @var InputSequenceFactory
     */
    private $inputSequenceFactory;

    /**
     * @var OptionDefinitionCollection
     */
    private $optionDefinitionCollection;

    /**
     * @var OptionDefinition
     */
    private $option;

    public function setUp(): void
    {
        $this->inputSequenceFactory = new InputSequenceFactory();
        $this->optionDefinitionCollection = new OptionDefinitionCollection();
        $this->option = new OptionDefinition(
            (new OptionValueFactory())->createFromTypeAndValue(OptionDefinition::TYPE_VALUE, 'default'),
            OptionDefinition::TYPE_VALUE,
            'z',
            'zexample'
        );
        $this->optionDefinitionCollection->add($this->option);
    }

    /**
     * @test
     */
    public function emptyInputSequence()
    {
        $result = $this->inputSequenceFactory->createFrom([''], $this->optionDefinitionCollection);

        $this->assertInstanceOf(InputSequence::class, $result);
        $this->assertCount(0, $result);
    }

    /**
     * @test
     */
    public function longOption()
    {
        $result = $this->inputSequenceFactory->createFrom(['--zexample=test'], $this->optionDefinitionCollection);

        $this->assertInstanceOf(InputSequence::class, $result);
        $this->assertCount(1, $result);
        $this->assertEquals('test', $result->getOption($this->option)->getValue());
    }

    /**
     * @test
     */
    public function shortOption()
    {
        $result = $this->inputSequenceFactory->createFrom(['-z=test'], $this->optionDefinitionCollection);

        $this->assertInstanceOf(InputSequence::class, $result);
        $this->assertCount(1, $result);
        $this->assertEquals('test', $result->getOption($this->option)->getValue());
    }

    /**
     * @test
     */
    public function invalidShortOption()
    {
        $result = $this->inputSequenceFactory->createFrom(['-i=test'], $this->optionDefinitionCollection);
        $this->assertNull($result);
    }

    /**
     * @test
     */
    public function invalidLongOption()
    {
        $result = $this->inputSequenceFactory->createFrom(['--invalid=test'], $this->optionDefinitionCollection);
        $this->assertNull($result);
    }

    /**
     * @test
     */
    public function invalidShortOptionForHelp()
    {
        $result = $this->inputSequenceFactory->createFrom(['-i=test'], $this->optionDefinitionCollection, true);

        $this->assertInstanceOf(InputSequence::class, $result);
        $this->assertCount(0, $result);
    }

    /**
     * @test
     */
    public function invalidLongOptionForHelp()
    {
        $result = $this->inputSequenceFactory->createFrom(['--invalid=test'], $this->optionDefinitionCollection, true);

        $this->assertInstanceOf(InputSequence::class, $result);
        $this->assertCount(0, $result);
    }

    /**
     * @test
     */
    public function doubleDashes()
    {
        $result = $this->inputSequenceFactory->createFrom(['--'], $this->optionDefinitionCollection);

        $this->assertInstanceOf(InputSequence::class, $result);
        $this->assertCount(0, $result);
    }
}
