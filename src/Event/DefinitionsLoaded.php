<?php

namespace my127\Workspace\Event;

use Symfony\Component\EventDispatcher\GenericEvent;

class DefinitionsLoaded extends GenericEvent
{
    public const EVENT = 'definitions.loaded';
}
