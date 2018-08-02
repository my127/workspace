<?php

namespace my127\Workspace\Event;

use Symfony\Component\EventDispatcher\Event;

class DefinitionsLoaded extends Event
{
    public const EVENT = 'definitions.loaded';
}
