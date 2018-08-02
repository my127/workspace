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
    const PRECEDENCE_HARNESS_DEFAULT    = 1;
    const PRECEDENCE_WORKSPACE_DEFAULT  = 2;
    const PRECEDENCE_GLOBAL_DEFAULT     = 3;

    const PRECEDENCE_HARNESS_NORMAL     = 4;
    const PRECEDENCE_WORKSPACE_NORMAL   = 5;
    const PRECEDENCE_GLOBAL_NORMAL      = 6;

    const PRECEDENCE_HARNESS_OVERRIDE   = 7;
    const PRECEDENCE_WORKSPACE_OVERRIDE = 8;
    const PRECEDENCE_GLOBAL_OVERRIDE    = 9;

    private $attributes;
    private $expressionLanguage;
    private $twigBuilder;

    private $precedenceMap =
    [
        WorkspaceDefinition::SCOPE_GLOBAL.Definition::PRIORITY_DEFAULT     => self::PRECEDENCE_GLOBAL_DEFAULT,
        WorkspaceDefinition::SCOPE_GLOBAL.Definition::PRIORITY_NORMAL      => self::PRECEDENCE_GLOBAL_NORMAL,
        WorkspaceDefinition::SCOPE_GLOBAL.Definition::PRIORITY_OVERRIDE    => self::PRECEDENCE_GLOBAL_OVERRIDE,

        WorkspaceDefinition::SCOPE_WORKSPACE.Definition::PRIORITY_DEFAULT  => self::PRECEDENCE_WORKSPACE_DEFAULT,
        WorkspaceDefinition::SCOPE_WORKSPACE.Definition::PRIORITY_NORMAL   => self::PRECEDENCE_WORKSPACE_NORMAL,
        WorkspaceDefinition::SCOPE_WORKSPACE.Definition::PRIORITY_OVERRIDE => self::PRECEDENCE_WORKSPACE_OVERRIDE,

        WorkspaceDefinition::SCOPE_HARNESS.Definition::PRIORITY_DEFAULT    => self::PRECEDENCE_HARNESS_DEFAULT,
        WorkspaceDefinition::SCOPE_HARNESS.Definition::PRIORITY_NORMAL     => self::PRECEDENCE_HARNESS_NORMAL,
        WorkspaceDefinition::SCOPE_HARNESS.Definition::PRIORITY_OVERRIDE   => self::PRECEDENCE_HARNESS_OVERRIDE,
    ];

    public function __construct(Collection $attributes, Expression $expressionLanguage, TwigEnvironmentBuilder $twigBuilder)
    {
        $this->attributes         = $attributes;
        $this->expressionLanguage = $expressionLanguage;
        $this->twigBuilder        = $twigBuilder;
    }

    public function build(DefinitionCollection $definitions)
    {
        foreach (DefinitionFactory::TYPES as $type) {
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
        return $this->precedenceMap[$definition->getScope().$definition->getPriority()];
    }
}
