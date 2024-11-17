<?php

namespace App\Core\Assistant\Factory;

use App\Core\Assistant\Dto\Messages\AssistantMessageDto;
use App\Core\Assistant\Dto\Messages\MessageDto;
use App\Core\Assistant\Dto\Messages\UserMessageDto;
use App\Core\Assistant\Facade\MessageTypeEnum;
use App\Models\ConversationMessage;

class MessageFactory
{
    public function create(string $type, ?int $id, ?string $message, ?string $name = null, ?string $imageUrl = null): MessageDto
    {
        if($type == MessageTypeEnum::ASSISTANT_MESSAGE->value){
            $messageDto = new AssistantMessageDto();
        } else if($type == MessageTypeEnum::USER_MESSAGE->value){
            $messageDto = new UserMessageDto();
        } else {
            throw new \Exception('Unknown message type');
        }

        if($name !== null)
            $messageDto->setName($name);

        if($imageUrl !== null)
            $messageDto->setImageUrl($imageUrl);

        $messageDto->setId($id);
        $messageDto->setMessage($message);

        return $messageDto;
    }

    public function createFromConversationMessage(ConversationMessage $conversationMessage): MessageDto
    {
        return $this->create(
            type: $conversationMessage->type_message,
            id: $conversationMessage->id,
            message: $conversationMessage->content,
        );
    }
}
