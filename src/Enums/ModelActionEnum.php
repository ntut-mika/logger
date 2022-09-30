<?php

namespace Mika\Logger\Enums;

enum ModelActionEnum: string
{
    case CREATE = 'CREATE';
    case UPDATE = 'UPDATE';
    case RESTORE = 'RESTORE';
    case SOFT_DELETE = 'SOFT_DELETE';
    case FORCE_DELETE = 'FORCE_DELETE';
}
