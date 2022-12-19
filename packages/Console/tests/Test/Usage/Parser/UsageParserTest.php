<?php

namespace Test\my127\Console\Usage\Parser;

use my127\Console\Usage\Model\BooleanOptionValue;
use my127\Console\Usage\Model\OptionDefinition;
use my127\Console\Usage\Model\OptionDefinitionCollection;
use my127\Console\Usage\Model\StringOptionValue;
use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../../../resources/usage.php';

class UsageParserTest extends TestCase
{
    /**
     * @test
     */
    public function anArgumentIsDefinedAsAnAlphaNumericStringBetweenAngleBracketsAndSpecifiedAsAnAlphaNumericString()
    {
        $result = usage('<environment>', 'development');

        $this->assertIsArray($result);
        $this->assertTrue(count($result) == 1);
    }

    /**
     * @test
     */
    public function anArgumentHasItsValueCapturedAndProvided()
    {
        $result = usage('<environment>', 'development');

        $this->assertIsArray($result);
        $this->assertTrue(count($result) == 1);
        $this->assertEquals("argument('environment', 'development')", $result[0]);
    }

    /**
     * @test
     */
    public function argumentsConsumeTheFirstAvailableToken()
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
    public function validWhenArgumentValueStartsWithAlphanumericCharacter()
    {
        $result = usage('<environment>', 'development');

        $this->assertIsArray($result);
        $this->assertTrue(count($result) == 1);
    }

    /**
     * @test
     */
    public function invalidWhenArgumentValueDoesNotStartWithAnAlphanumericCharacter()
    {
        $result = usage('<environment>', '-d');

        $this->assertIsBool($result);
        $this->assertFalse($result);
    }

    /**
     * @test
     */
    public function argumentsAreRequiredByDefault()
    {
        $result = usage('<environment>', '');
        $this->assertFalse($result);
    }

    /**
     * @test
     */
    public function argumentsCanBeMadeOptional()
    {
        $result = usage('[<environment>]', '');

        $this->assertIsArray($result);
        $this->assertTrue(count($result) == 0);
    }

    /**
     * @test
     */
    public function aCommandIsDenotedByAnAlphanumericalString()
    {
        $result = usage('command', 'command');

        $this->assertIsArray($result);
        $this->assertTrue(count($result) == 1);
        $this->assertEquals("command('command')", $result[0]);
    }

    /**
     * @test
     */
    public function aCommandByDefaultIsRequired()
    {
        $result = usage('command', '');

        $this->assertIsBool($result);
        $this->assertEquals(false, $result);
    }

    /**
     * @test
     */
    public function validWhenCommandsSpecifiedInTheOrderDefined()
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
    public function invalidWhenCommandsSpecifiedInDifferentOrderToThatDefined()
    {
        $result = usage('foo bar', 'bar foo');

        $this->assertIsBool($result);
        $this->assertFalse($result);
    }

    /**
     * @test
     */
    public function commandsAreRequiredByDefault()
    {
        $result = usage('foo', '');
        $this->assertFalse($result);
    }

    /**
     * @test
     */
    public function commandsCanBeMadeOptional()
    {
        $result = usage('[foo]', '');
        $this->assertIsArray($result);
        $this->assertTrue(count($result) == 0);
    }

    /**
     * @test
     */
    public function aSingleDashWhenNotPartOfAnOptionIsTreatedAsCommand()
    {
        $result = usage('-', '-');
        $this->assertIsArray($result);
        $this->assertEquals(1, count($result));
        $this->assertEquals("command('-')", $result[0]);
    }

    /**
     * @test
     */
    public function shortOptionIsTreatedAsAFlagWithATrueValue()
    {
        $result = usage('-h', '-h');

        $this->assertIsArray($result);
        $this->assertTrue(count($result) == 1);
        $this->assertEquals("option('h', 'true')", $result[0]);
    }

    /**
     * @test
     */
    public function optionsAreOrderlessAndProvidedInTheOrderDefined()
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
    public function shortOptionsCanBeDescribedAsAShortSequenceAndSpecifiedAsSeparateOptions()
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
    public function shortOptionsCanBeDescribedAsSeparateOptionsAndSpecifiedAsAShortSequence()
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
    public function shortOptionsCanBeDescribedAndSpecifiedAsAShortSequence()
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
    public function longOptionCanHaveValueSpecifiedAfterSpace()
    {
        $result = usage('--environment=<env>', '--environment development');

        $this->assertIsArray($result);
        $this->assertEquals(1, count($result));
        $this->assertEquals("option('environment', 'development')", $result[0]);
    }

    /**
     * @test
     */
    public function longOptionCanHaveValueSpecifiedAfterEquals()
    {
        $result = usage('--environment=<env>', '--environment=development');

        $this->assertIsArray($result);
        $this->assertEquals(1, count($result));
        $this->assertEquals("option('environment', 'development')", $result[0]);
    }

    /**
     * @test
     */
    public function shortOptionCanHaveValueSpecifiedWithoutSpaceOrEquals()
    {
        $result = usage('-e=<env>', '-eDevelopment');

        $this->assertIsArray($result);
        $this->assertEquals(1, count($result));
        $this->assertEquals("option('e', 'Development')", $result[0]);
    }

    /**
     * @test
     */
    public function optionsAreRequiredByDefault()
    {
        $result = usage('-e=<env>', '');

        $this->assertIsBool($result);
        $this->assertFalse($result);
    }

    /**
     * @test
     */
    public function optionsCanBeMadeOptional()
    {
        $result = usage('[-e=<env>]', '');

        $this->assertIsArray($result);
        $this->assertEquals(0, count($result));
    }

    /**
     * @test
     */
    public function optionDefinitionsCanAllBeAddedAtOnce()
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
    public function passedOptionsMustBePartOfUsageDefinition()
    {
        $result = usage('[-hv] foo bar', '--unknown foo bar');
        $this->assertFalse($result);
    }
}
