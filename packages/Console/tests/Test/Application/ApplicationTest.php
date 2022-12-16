<?php

namespace Test\my127\Console\Application;

use my127\Console\Application\Event\BeforeActionEvent;
use my127\Console\Application\Executor;
use my127\Console\Console;
use my127\Console\Usage\Input;
use my127\Console\Usage\Model\BooleanOptionValue;
use PHPUnit\Framework\TestCase;

class ApplicationTest extends TestCase
{
    /**
     * Ensures that by default [options] is added to usage
     * definitions.
     *
     * @test
     */
    public function options_are_added_to_usage_definitions_by_default()
    {
        $application = Console::application('foo')
            ->option('-d, --debug')
            ->option('-V, --verbose')
            ->action(
                function (Input $input) {
                    $this->assertEquals(BooleanOptionValue::create(false), $input->getOption('debug'));
                    $this->assertEquals(BooleanOptionValue::create(false), $input->getOption('verbose'));
                }
            );

        $application->run(['foo']);
    }

    /**
     * @test
     */
    public function when_no_usage_is_specified_action_is_still_triggered_by_usage_matching_context()
    {
        $triggered = false;

        $application = Console::application('foo');

        $application
            ->section('bar')
            ->action(
                function () use (&$triggered) {
                    $triggered = true;
                }
            );

        $application->run(['foo', 'bar']);

        $this->assertTrue($triggered);
    }

    /**
     * @test
     */
    public function invalid_usage_event_dispatched_when_no_usage_match_found()
    {
        $triggered = false;

        $application = Console::application('foo')
            ->on(
                Executor::EVENT_INVALID_USAGE, function () use (&$triggered) {
                    $triggered = true;
                }
            )
            ->usage('bar')
            ->action(
                function () {
                    $this->fail('I should not be able to get here.');
                }
            );

        $this->expectOutputRegex('/Usage:/');

        $application->run([]);

        $this->assertTrue($triggered);
    }

    /**
     * @test
     */
    public function event_triggered_before_context_actions_invoked()
    {
        $triggered = false;

        $application = Console::application('foo');

        $application
            ->section('bar')
            ->action(
                function () {
                }
            );

        $application->on(
            Executor::EVENT_BEFORE_ACTION, function () use (&$triggered) {
                $triggered = true;
            }
        );
        $application->run(['foo', 'bar']);

        $this->assertTrue($triggered);
    }

    /**
     * @test
     */
    public function context_actions_can_be_stopped_from_running()
    {
        $triggered = false;

        $application = Console::application('foo')
            ->usage('bar')
            ->action(
                function () use (&$triggered) {
                    $triggered = true; 
                }
            );

        $application->on(
            Executor::EVENT_BEFORE_ACTION, function (BeforeActionEvent $event) {
                $event->preventAction(); 
            }
        );
        $application->run(['foo', 'bar']);

        $this->assertFalse($triggered);
    }

    /**
     * @test
     */
    public function actions_can_take_argument()
    {
        $actionResult = '';
        $application = Console::application('foo');
        $application
            ->register(function (Input $input) use (&$actionResult) {
                $actionResult = sprintf(
                    'Action "%s" executed with argument "%s".',
                    implode(' ', $input->getCommand()),
                    $input->getArgument('%')
                );
            }, 'test')
            ->action('test')->usage('test %');

        $application->run(['foo', 'test', '1234']);

        $this->assertEquals('Action "foo test" executed with argument "1234".', $actionResult);
    }
}