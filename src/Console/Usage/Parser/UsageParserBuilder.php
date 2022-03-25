<?php

namespace my127\Workspace\Console\Usage\Parser;

use Exception;
use my127\Workspace\Console\Usage\Model\OptionDefinition;
use my127\Workspace\Console\Usage\Model\OptionDefinitionCollection;
use my127\Workspace\Console\Usage\Parser\Transition\ArgumentTransition;
use my127\Workspace\Console\Usage\Parser\Transition\CommandTransition;
use my127\Workspace\Console\Usage\Parser\Transition\LoopTransition;
use my127\Workspace\Console\Usage\Parser\Transition\OptionTransition;
use my127\Workspace\Console\Usage\Parser\Transition\ShortcutTransition;
use my127\Workspace\Console\Usage\Scanner\Scanner;
use my127\Workspace\Console\Usage\Scanner\Token;
use my127\Workspace\FSM\Definition;
use my127\Workspace\FSM\State\State;
use my127\Workspace\Console\Usage\Parser\UsageParser;

class UsageParserBuilder
{
    /**
     * @var OptionDefinitionCollection
     */
    private $globalDefinitionRepository;

    /**
     * @var OptionDefinitionCollection
     */
    private $usageDefinitionRepository;

    /**
     * @var Scanner
     */
    private $tokens;

    const MODE_OPTIONAL = 'optional';
    const MODE_REQUIRED = 'required';

    private $stack = [];

    /**
     * @var Definition[][]
     */
    private $sequences = [];

    /**
     * @var Definition[]
     */
    private $sequence = [];

    private $mode = self::MODE_REQUIRED;

    /**
     * Create Command Parser
     *
     * @param string                     $definition
     * @param OptionDefinitionCollection $definitionRepository
     *
     * @return UsageParser
     */
    public function createUsageParser($definition, OptionDefinitionCollection $definitionRepository = null)
    {
        $this->stack                      = [];
        $this->sequences                  = [];
        $this->sequence                   = [];
        $this->mode                       = self::MODE_REQUIRED;
        $this->globalDefinitionRepository = (!$definitionRepository) ? new OptionDefinitionCollection() : $definitionRepository;
        $this->usageDefinitionRepository  = new OptionDefinitionCollection();
        $this->tokens                     = new Scanner($definition);

        return new UsageParser($this->parse(), $this->usageDefinitionRepository);
    }

    private function parse()
    {
        while (($type = $this->tokens->peek()->getType()) != Token::T_EOL) {
            switch ($type) {
                case Token::T_SHORT_OPTION:
                case Token::T_LONG_OPTION:
                    $this->parseOption();
                    break;

                case Token::T_SINGLE_DASH:
                    $this->parseSingleDash();
                    break;

                case Token::T_OPTION_SEQUENCE:
                    $this->parseOptionSequence();
                    break;

                case Token::T_ARGUMENT_START:
                    $this->parseArgument();
                    break;

                case Token::T_STRING:
                    $this->parseCommand();
                    break;

                case Token::T_REQUIRED_START:
                    $this->parseRequired();
                    break;

                case Token::T_OPTIONAL_START:
                    $this->parseOptional();
                    break;

                case Token::T_DOUBLE_DASH:
                    $this->parseDoubleDash();
                    break;

                case Token::T_MUTEX:
                    $this->parseMutex();
                    break;

                case Token::T_ELLIPSIS:
                    $this->parseEllipsis();
                    break;

                case Token::T_OPTIONS:
                    $this->parseOptions();
                    break;

                default:
                    return null;
            }
        }

        $usage = $this->groupSequences();
        $usage->getState('start')->setType(State::TYPE_INITIAL);
        $usage->getState('end')->setType(State::TYPE_TERMINAL);

        return $usage;
    }

    private function addOption(OptionDefinition $optionDefinition)
    {
        $this->usageDefinitionRepository->add($optionDefinition);
        $option = new Definition('Short Option');
        $option->addTransition(new OptionTransition($optionDefinition, $option->getState('end')), 'start');
        $this->append($option);
    }

    private function parseOptions()
    {
        $this->expect(Token::T_OPTIONS);

        foreach ($this->globalDefinitionRepository as $optionDefinition) {
            $this->addOption($optionDefinition);
        }
    }

    private function parseMutex()
    {
        $this->expect(Token::T_MUTEX);
        $this->sequences[] = $this->sequence;
        $this->sequence    = [];
    }

    private function parseEllipsis()
    {
        $this->expect(Token::T_ELLIPSIS);

        if (!($atom = end($this->sequence))) {
            throw new Exception('Unable to loop empty atom');
        }

        $atom->getState('end')->addTransition(new LoopTransition($atom->getState('start')));
    }

    private function parseArgument()
    {
        $token = $this->expect([Token::T_ARGUMENT_START, Token::T_STRING, Token::T_ARGUMENT_STOP])[1];
        $argument = new Definition('Argument');
        $argument->addTransition(new ArgumentTransition($token->getValue(), $argument->getState('end')), 'start');
        $this->append($argument);
    }

    private function parseCommand()
    {
        $token = $this->expect([Token::T_STRING])[0];
        $command = new Definition('Command');
        $command->addTransition(new CommandTransition($token->getValue(), $command->getState('end')), 'start');
        $this->append($command);
    }

    private function parseRequired()
    {
        $this->expect(Token::T_REQUIRED_START);
        $this->push(self::MODE_REQUIRED);
        $this->parse();
        $this->pop();
        $this->expect(Token::T_REQUIRED_STOP);
    }

    private function parseOptional()
    {
        $this->expect(Token::T_OPTIONAL_START);
        $this->push(self::MODE_OPTIONAL);
        $this->parse();
        $this->pop();
        $this->expect(Token::T_OPTIONAL_STOP);
    }

    private function parseOption()
    {
        $token      = $this->tokens->pop();
        $definition = $this->globalDefinitionRepository->find($token->getValue());
        $value      = $this->is(Token::T_EQUALS);

        if (!$definition) {
            $definition = ($token->getType() == Token::T_LONG_OPTION) ?
                new OptionDefinition(null, $token->getValue(), null, ($value ? 'value' : 'bool')):
                new OptionDefinition($token->getValue(), null, null, ($value ? 'value' : 'bool'));
        }

        if ($value) {
            $this->expect([Token::T_ARGUMENT_START, Token::T_STRING, Token::T_ARGUMENT_STOP]);
        }

        $this->addOption($definition);
    }

    private function parseOptionSequence()
    {
        $tokens = $this->tokens->pop()->getValue();

        for ($i = 0; $i < strlen($tokens); ++$i) {
            if (!($definition = $this->globalDefinitionRepository->find($tokens[$i]))) {
                $definition = new OptionDefinition($tokens[$i], null, null, 'bool');
            }

            $this->addOption($definition);
        }
    }

    private function parseDoubleDash()
    {
    }

    private function parseSingleDash()
    {
        $this->expect(Token::T_SINGLE_DASH);
        $command = new Definition('Command');
        $command->addTransition(new CommandTransition('-', $command->getState('end')), 'start');
        $this->append($command);
    }

    private function expect($types)
    {
        $passed = [];
        $types  = (is_array($types)) ? $types : [$types];

        foreach ($types as $type) {
            $passed[] = $token = $this->tokens->pop();

            if ($token->getType() != $type) {
                throw new Exception('Expected Token ['.(new Token($type)).'] but found ['.(new Token($token->getType())).'].');
            }
        }

        return $passed;
    }

    private function is($type)
    {
        return ($this->tokens->peek()->getType() == $type) ? $this->tokens->pop() : false;
    }

    private function append(Definition $partial)
    {
        if (count($this->sequence) > 0) {
            end($this->sequence)->getState('end')->addTransition(new ShortcutTransition($partial->getState('start')));
        }

        if ($this->mode == self::MODE_OPTIONAL) {
            $partial->getState('start')->addTransition(new ShortcutTransition($partial->getState('end')));
        }

        $this->sequence[] = $partial;
    }

    private function groupSequences()
    {
        if (empty($this->sequences) && empty($this->sequence)) {
            return null;
        }

        $group = new Definition('Group');
        $choices = array_merge($this->sequences, [$this->sequence]);

        foreach ($choices as $choice) {
            if (count($choice) == 0) {
                continue;
            }

            $group->getState('start')->addTransition(new ShortcutTransition($choice[0]->getState('start')));
            end($choice)->getState('end')->addTransition(new ShortcutTransition($group->getState('end')));
        }

        return $group;
    }

    private function push($mode)
    {
        $this->stack[] = [$this->mode, $this->sequence, $this->sequences];

        $this->mode      = $mode;
        $this->sequence  = [];
        $this->sequences = [];
    }

    private function pop()
    {
        $group = $this->groupSequences();
        list ($this->mode, $this->sequence, $this->sequences) = array_pop($this->stack);

        if ($group !== null) {
            $this->append($group);
        }
    }
}
