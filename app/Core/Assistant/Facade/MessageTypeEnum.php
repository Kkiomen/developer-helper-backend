<?php

namespace App\Core\Assistant\Facade;

enum MessageTypeEnum: string
{
    case ASSISTANT_MESSAGE = 'assistant';
    case USER_MESSAGE = 'user';
}
