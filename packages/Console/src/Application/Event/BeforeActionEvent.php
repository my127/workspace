<?php

namespace my127\Console\Application\Event;

use my127\Console\Application\Section\Section;
use my127\Console\Usage\Input;
use Symfony\Component\EventDispatcher\Event;

class BeforeActionEvent extends Event
{
    private $input;
    private $preventAction = false;
    private $section;

    public function __construct(Input $input, Section $section)
    {
        $this->input   = $input;
        $this->section = $section;
    }

    public function getSection(): Section
    {
        return $this->section;
    }

    public function getInput(): Input
    {
        return $this->input;
    }

    public function isActionPrevented(): bool
    {
        return $this->preventAction;
    }

    public function preventAction(): void
    {
        $this->preventAction = true;
    }
}
