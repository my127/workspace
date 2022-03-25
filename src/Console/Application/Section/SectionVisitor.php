<?php

namespace my127\Workspace\Console\Application\Section;

use my127\Workspace\Console\Application\Section\Section;

interface SectionVisitor
{
    /**
     * @return bool
     *    true: continue traversing over sections
     *    false: no need to continue, stop traversing
     */
    public function visit(Section $section): bool;
}
