<?php

namespace NovaExportConfiguration\Enums;

enum ConfigValueType: string
{
    case SKIP   = 'skip';
    case EMPTY  = 'empty';
    case FILLED = 'filled';

    case ACTIVE   = 'active';
    case INACTIVE = 'inactive';

    case OR  = 'or';
    case AND = 'and';
}
