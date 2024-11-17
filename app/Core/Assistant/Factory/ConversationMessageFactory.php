<?php

namespace App\Core\Assistant\Factory;

use App\Core\Assistant\Facade\MessageTypeEnum;
use App\Models\ConversationMessage;

/**
 * Class MessageFactory - Factory for creating new conversation message
 */
class ConversationMessageFactory
{
    /**
     * Create new conversation message
     * @param int|null $conversationId
     * @param int|null $userId
     * @param string|null $userMessage
     * @param string|null $systemPrompt
     * @param string|null $content
     * @param string|null $actionType
     * @param MessageTypeEnum|null $typeMessage
     * @return ConversationMessage
     */
    public function createNewConversationMessage(?int $conversationId, ?int $userId, ?string $userMessage, ?string $systemPrompt, ?string $content = null, ?string $actionType = null, ?MessageTypeEnum $typeMessage = null): ConversationMessage
    {
        $message = new ConversationMessage();
        $message->conversation_id = $conversationId;
        $message->user_id = $userId;
        $message->userPrompt = $userMessage;
        $message->systemPrompt = $systemPrompt;
        $message->action_type = $actionType;
        $message->type_message = $typeMessage !== null ? $typeMessage->value : $typeMessage;
        $message->content = $content;
        $message->save();

        return $message;
    }
}
