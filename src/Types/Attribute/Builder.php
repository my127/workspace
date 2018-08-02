<?php

namespace my127\Workspace\Types\Attribute;

use Exception;
use my127\Workspace\Definition\Collection as DefinitionCollection;
use my127\Workspace\Definition\Definition as WorkspaceDefinition;
use my127\Workspace\Environment\Builder as EnvironmentBuilder;
use my127\Workspace\Expression\Expression;
use my127\Workspace\Twig\EnvironmentBuilder as TwigEnvironmentBuilder;
use Symfony\Component\ExpressionLanguage\ExpressionFunction;

class Builder implements EnvironmentBuilder
{
    const PRECEDENCE_HARNESS_STANDARD   = 2;
    const PRECEDENCE_GLOBAL_STANDARD    = 4;
    const PRECEDENCE_WORKSPACE_STANDARD = 6;


    private $attributes;
    private $expressionLanguage;
    private $twigBuilder;

    public function __construct(Collection $attributes, Expression $expressionLanguage, TwigEnvironmentBuilder $twigBuilder)
    {
        $this->attributes         = $attributes;
        $this->expressionLanguage = $expressionLanguage;
        $this->twigBuilder        = $twigBuilder;
    }

    public function build(DefinitionCollection $definitions)
    {
        foreach (['attribute', 'attributes'] as $type) {
            foreach ($definitions->findByType($type) as $definition) {
                /** @var Definition $definition */
                if ($definition->getKey() == '~') {
                    $this->attributes->add($definition->getValue(), $this->resolveAttributePrecedence($definition));
                } else {
                    $this->attributes->set($definition->getKey(), $definition->getValue(), $this->resolveAttributePrecedence($definition));
                }
            }
        }

        $this->expressionLanguage->addFunction(new ExpressionFunction('attr',
            function () {
                throw new Exception("Compilation of the 'get' function within Types\Attribute\Builder is not supported.");
            },
            function ($arguments, $key, $default = null) {
                return $this->attributes->get($key, $default);
            })
        );

        $this->twigBuilder->addFunction('attr', function($key, $default = null) {
            return $this->attributes->get($key, $default);
        });
    }

    private function resolveAttributePrecedence(Definition $definition): int
    {
        switch ($definition->getScope()) {

            case WorkspaceDefinition::SCOPE_HARNESS:
                return self::PRECEDENCE_HARNESS_STANDARD;

            case WorkspaceDefinition::SCOPE_WORKSPACE:
                return self::PRECEDENCE_WORKSPACE_STANDARD;
        }

        return self::PRECEDENCE_GLOBAL_STANDARD;
    }
}
