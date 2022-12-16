<?php

namespace my127\Workspace\Event;

use Symfony\Component\EventDispatcher\GenericEvent;

class EnvironmentBuilt extends GenericEvent
{
    public const EVENT = 'environment.built';
}
