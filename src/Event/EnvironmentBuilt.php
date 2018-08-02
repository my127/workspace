<?php

namespace my127\Workspace\Event;

use Symfony\Component\EventDispatcher\Event;

class EnvironmentBuilt extends Event
{
    public const EVENT = 'environment.built';
}
