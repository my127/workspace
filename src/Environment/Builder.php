<?php

namespace my127\Workspace\Environment;

use my127\Workspace\Definition\Collection as DefinitionCollection;

interface Builder
{
    public function build(DefinitionCollection $definitions);
}
