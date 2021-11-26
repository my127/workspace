<?php

namespace my127\Workspace\Types\Crypt;

use Exception;
use my127\Console\Usage\Input;
use my127\Console\Usage\Model\OptionValue;
use my127\Workspace\Application;
use my127\Workspace\Definition\Collection as DefinitionCollection;
use my127\Workspace\Environment\Builder as EnvironmentBuilder;
use my127\Workspace\Environment\Environment;
use my127\Workspace\Expression\Expression;
use my127\Workspace\Twig\EnvironmentBuilder as TwigEnvironmentBuilder;
use Symfony\Component\ExpressionLanguage\ExpressionFunction;

class Builder implements EnvironmentBuilder
{
    private $crypt;
    private $expressionLanguage;
    private $twigBuilder;
    private $application;

    public function __construct(Application $application, Crypt $crypt, Expression $expressionLanguage, TwigEnvironmentBuilder $twigBuilder)
    {
        $this->crypt = $crypt;
        $this->expressionLanguage = $expressionLanguage;
        $this->twigBuilder = $twigBuilder;
        $this->application = $application;
    }

    public function build(Environment $environment, DefinitionCollection $definitions)
    {
        foreach ($definitions->findByType(KeyDefinition::TYPE) as $definition) {
            /* @var KeyDefinition $definition */
            $this->crypt->addKey(new Key($definition->getName(), $definition->getKey()));
        }

        if (($default = getenv('MY127WS_KEY')) !== false) {
            $this->crypt->addKey(new Key('default', $default));
        }

        foreach (getenv() as $key => $value) {
            if (0 !== strpos($key, 'MY127WS_KEY_')) {
                continue;
            }

            $this->crypt->addKey(new Key(strtolower(substr($key, strrpos($key, '_'))), $value));
        }

        $this->expressionLanguage->addFunction(new ExpressionFunction('decrypt',
            function () {
                throw new Exception("Compilation of the 'decrypt' function within Types\Crypt\Builder is not supported.");
            },
            function ($arguments, $encrypted) {
                return $this->crypt->decrypt($encrypted);
            })
        );

        $this->twigBuilder->addFunction('decrypt', function ($encrypted) {
            return $this->crypt->decrypt($encrypted);
        });

        if ($definitions->hasType(KeyDefinition::TYPE)) {
            $this->application->section('secret encrypt')
                ->usage('secret encrypt <message> [<key>]')
                ->action(function (Input $input) {
                    $key = $input->getArgument('key');
                    $key = $key instanceof OptionValue ? $key->value() : $key;
                    $key = $key ?? 'default';
                    echo $this->crypt->encrypt($input->getArgument('message'), $key)."\n";
                });

            $this->application->section('secret decrypt')
                ->usage('secret decrypt <encrypted>')
                ->action(function (Input $input) {
                    echo $this->crypt->decrypt($input->getArgument('encrypted'))."\n";
                });
        }

        $this->application->section('secret generate-random-key')
            ->action(function (Input $input) {
                echo (new Key('random'))->getKeyAsHex()."\n";
            });
    }
}
