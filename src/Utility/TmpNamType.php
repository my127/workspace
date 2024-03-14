<?php

declare(strict_types=1);

namespace my127\Workspace\Utility;

enum TmpNamType: string
{
    case DIR = 'directory';
    case FILE = 'file';
    case PATH = 'path';
}
