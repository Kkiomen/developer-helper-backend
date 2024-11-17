<?php

namespace App\Core\Assistant\Prompt\Abstract\Enum;

enum OpenApiResultType: string
{
    case NORMAL = 'normal';
    case JSON_OBJECT = 'json_object';
}
