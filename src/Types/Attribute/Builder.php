<?php

namespace my127\Workspace\Types\Attribute;

use Exception;
use my127\Workspace\Definition\Collection as DefinitionCollection;
use my127\Workspace\Definition\Definition as WorkspaceDefinition;
use my127\Workspace\Environment\Builder as EnvironmentBuilder;
use my127\Workspace\Environment\Environment;
use my127\Workspace\Expression\Expression;
use my127\Workspace\Twig\EnvironmentBuilder as TwigEnvironmentBuilder;
use Symfony\Component\ExpressionLanguage\ExpressionFunction;
use Symfony\Component\Yaml\Yaml;

class Builder implements EnvironmentBuilder
{
    public const PRECEDENCE_HARNESS_DEFAULT = 1;
    public const PRECEDENCE_WORKSPACE_DEFAULT = 2;
    public const PRECEDENCE_GLOBAL_DEFAULT = 3;

    public const PRECEDENCE_HARNESS_NORMAL = 4;
    public const PRECEDENCE_WORKSPACE_NORMAL = 5;
    public const PRECEDENCE_GLOBAL_NORMAL = 6;

    public const PRECEDENCE_HARNESS_OVERRIDE = 7;
    public const PRECEDENCE_WORKSPACE_OVERRIDE = 8;
    public const PRECEDENCE_GLOBAL_OVERRIDE = 9;

    private $attributes;
    private $expressionLanguage;
    private $twigBuilder;

    private $precedenceMap =
    [
        WorkspaceDefinition::SCOPE_GLOBAL . Definition::PRIORITY_DEFAULT => self::PRECEDENCE_GLOBAL_DEFAULT,
        WorkspaceDefinition::SCOPE_GLOBAL . Definition::PRIORITY_NORMAL => self::PRECEDENCE_GLOBAL_NORMAL,
        WorkspaceDefinition::SCOPE_GLOBAL . Definition::PRIORITY_OVERRIDE => self::PRECEDENCE_GLOBAL_OVERRIDE,

        WorkspaceDefinition::SCOPE_WORKSPACE . Definition::PRIORITY_DEFAULT => self::PRECEDENCE_WORKSPACE_DEFAULT,
        WorkspaceDefinition::SCOPE_WORKSPACE . Definition::PRIORITY_NORMAL => self::PRECEDENCE_WORKSPACE_NORMAL,
        WorkspaceDefinition::SCOPE_WORKSPACE . Definition::PRIORITY_OVERRIDE => self::PRECEDENCE_WORKSPACE_OVERRIDE,

        WorkspaceDefinition::SCOPE_HARNESS . Definition::PRIORITY_DEFAULT => self::PRECEDENCE_HARNESS_DEFAULT,
        WorkspaceDefinition::SCOPE_HARNESS . Definition::PRIORITY_NORMAL => self::PRECEDENCE_HARNESS_NORMAL,
        WorkspaceDefinition::SCOPE_HARNESS . Definition::PRIORITY_OVERRIDE => self::PRECEDENCE_HARNESS_OVERRIDE,
    ];

    public function __construct(Collection $attributes, Expression $expressionLanguage, TwigEnvironmentBuilder $twigBuilder)
    {
        $this->attributes = $attributes;
        $this->expressionLanguage = $expressionLanguage;
        $this->twigBuilder = $twigBuilder;
    }

    public function build(Environment $environment, DefinitionCollection $definitions)
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

        foreach (getenv() as $key => $value) {
            if (strpos($key, 'MY127WS_ATTR_') !== 0) {
                continue;
            }

            $attributes = Yaml::parse($value);

            if (is_string($attributes)) {
                // @todo debug in jenkins as to whats going on
                // some ci environments (jenkins) double quote the env variable
                $attributes = Yaml::parse($attributes);
            }

            if (!is_array($attributes)) {
                throw new Exception('MY127WS_ATTRIBUTES must be a YAML object.');
            }

            $this->attributes->add($attributes, 10);
        }

        $this->expressionLanguage->addFunction(new ExpressionFunction('attr',
            function () {
                throw new Exception("Compilation of the 'get' function within Types\Attribute\Builder is not supported.");
            },
            function ($arguments, $key, $default = null) {
                return $this->attributes->get($key, $default);
            })
        );

        $this->twigBuilder->addFunction('attr', function ($key, $default = null) {
            return $this->attributes->get($key, $default);
        });
    }

    private function resolveAttributePrecedence(Definition $definition): int
    {
        return $this->precedenceMap[$definition->getScope() . $definition->getPriority()];
    }
}
