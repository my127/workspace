<?php

namespace my127\Console\Application\Section;

interface SectionVisitor
{
    /**
     * @return bool
     *    true: continue traversing over sections
     *    false: no need to continue, stop traversing
     */
    public function visit(Section $section): bool;
}
