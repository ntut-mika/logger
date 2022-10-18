<?php

namespace Mika\Logger\Enums;

enum LogTypeEnum: string
{
    case Request = 'request';
    case Model = 'model';
}
