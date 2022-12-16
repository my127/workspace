<?php

namespace my127\Console\Application\Section;

class Section
{
    private $name = null;
    private $description = null;
    private $usageDefinitions = [];
    private $options = [];
    private $action = null;

    /**
     * @var Section[]
     */
    private $children = [];

    public function __construct($name)
    {
        $this->name = $name;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): void
    {
        $this->description = $description;
    }

    public function description(string $description): Section
    {
        $this->setDescription($description);

        return $this;
    }

    public function getUsageDefinitions(): iterable
    {
        return $this->usageDefinitions;
    }

    public function addUsageDefinition(string $usage): void
    {
        $this->usageDefinitions[] = strtok($this->name, ' ') .
            ' ' .
            ((strpos($usage, '[options]') === false && $usage[-1] != '%') ? $usage . ' [options]' : $usage);
    }

    public function usage(string $usage): Section
    {
        $this->addUsageDefinition($usage);

        return $this;
    }

    public function getOptions(): iterable
    {
        return $this->options;
    }

    public function addOption(string $option): void
    {
        $this->options[] = $option;
    }

    public function option(string $option): Section
    {
        $this->addOption($option);

        return $this;
    }

    public function getAction()
    {
        return $this->action;
    }

    public function setAction($action): void
    {
        $this->action = $action;
    }

    public function action($action): Section
    {
        $this->setAction($action);

        return $this;
    }

    public function add(Section $child): void
    {
        $this->children[] = $child;
    }

    public function get(string $name): Section
    {
        if ($name === $this->name) {
            return $this;
        }

        foreach ($this->children as $child) {
            if (strpos($name, $child->getName()) === 0) {
                return $child->get($name);
            }
        }

        $childSectionName = substr($name, 0, strpos($name, ' ', strlen($this->name) + 1) ?: strlen($name));
        $childSection = new self($childSectionName);

        $this->add($childSection);

        return $childSection->get($name);
    }

    public function getChildren(): array
    {
        return $this->children;
    }

    public function contains(string $name): bool
    {
        if ($name === $this->name) {
            return true;
        }

        foreach ($this->children as $child) {
            if ($child->contains($name)) {
                return true;
            }
        }

        return false;
    }

    public function accept(SectionVisitor $visitor): bool
    {
        if ($visitor->visit($this) === false) {
            return false;
        }

        foreach ($this->children as $child) {
            if ($child->accept($visitor) === false) {
                return false;
            }
        }

        return true;
    }
}
