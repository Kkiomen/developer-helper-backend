<?php

namespace App\Core\Assistant\Service;

use App\Core\Assistant\Dto\Messages\MessageDto;
use App\Core\Assistant\Facade\MessageTypeEnum;
use App\Core\Assistant\Factory\ConversationFactory;
use App\Core\Assistant\Factory\ConversationMessageFactory;
use App\Core\Assistant\Factory\MessageFactory;
use App\Models\Conversation;
use Illuminate\Support\Facades\Auth;

/**
 * Class ConversationService - Service for conversation handling
 */
class ConversationService
{
    public function __construct(
        private ConversationFactory $conversationFactory,
        private ConversationMessageFactory $conversationMessageFactory,
        private MessageFactory $messageFactory
    ){}

    /**
     * Create new conversation
     * @param int|null $userId
     * @param bool $active
     * @return Conversation
     */
    public function createConversation(?int $userId = null, bool $active = true): Conversation
    {
        return $this->conversationFactory->createNewConversation($userId);

    }

    /**
     * Get conversation by hash
     * @param string $hash
     * @return Conversation|null
     */
    public function getConversationByHash(string $hash): ?Conversation
    {
        return Conversation::where('session_hash', $hash)->first();
    }

    /**
     * Get active conversation by user id
     * @param int $userId
     * @return Conversation|null
     */
    public function getActiveConversationByUserId(int $userId): ?Conversation
    {
        return Conversation::where('user_id', $userId)->where('active', true)->first();
    }

    /**
     * Close conversation
     * @param Conversation $conversation
     */
    public function closeConversation(Conversation $conversation)
    {
        $conversation->active = false;
        $conversation->save();
    }

    /**
     * Activate conversation
     * @param Conversation $conversation
     */
    public function activateConversation(Conversation $conversation)
    {
        $conversation->active = true;
        $conversation->save();
    }

    /**
     * Check if conversation is active
     * @param Conversation $conversation
     * @return bool
     */
    public function isConversationActive(Conversation $conversation): bool
    {
        return $conversation->active;
    }

    /**
     * Get conversation by user id
     * @param int $userId
     * @return Conversation|null
     */
    public function getOrCreateSessionHashToActiveConversationByUserId(int $userId): ?string
    {
        $conversation = $this->getActiveConversationByUserId($userId);
        if($conversation){
            return $conversation->session_hash;
        }

        return $this->conversationFactory->createNewConversation($userId)->session_hash;
    }

    /**
     * Register new message in conversation
     * @param string $conversationHash
     * @param string $userMessage
     * @param string $systemPrompt
     * @param string $generatedContent
     * @return void
     */
    public function registerNewAiMessages(string $conversationHash, string $userMessage, string $systemPrompt, string $generatedContent): void
    {
        $conversation = $this->getConversationByHash($conversationHash);
        if($conversation){

            // Create user message
            $this->conversationMessageFactory->createNewConversationMessage(
                conversationId: $conversation->id,
                userId: Auth::id(),
                userMessage: $userMessage,
                systemPrompt: $systemPrompt,
                content: $userMessage,
                typeMessage: MessageTypeEnum::USER_MESSAGE
            );

            // Create assistant message
            $this->conversationMessageFactory->createNewConversationMessage(
                conversationId: $conversation->id,
                userId: Auth::id(),
                userMessage: $userMessage,
                systemPrompt: $systemPrompt,
                content: $generatedContent,
                typeMessage: MessageTypeEnum::ASSISTANT_MESSAGE
            );
        }
    }

    public function getConversationMessagesByHash(?string $conversationHash): array
    {
        $messages = Conversation::where('session_hash', $conversationHash)->first()->messages;
        $resultMessages = [];

        foreach ($messages as $message){
            /** @var MessageDto $messageDto */
            $messageDto = $this->messageFactory->createFromConversationMessage($message);
            $resultMessages[] = $messageDto->toArray();
        }

        return $resultMessages;
    }

    /**
     * Reset conversation
     * @param string $conversationHash
     * @return string
     */
    public function resetConversation(string $conversationHash): string
    {
        $conversation = $this->getConversationByHash($conversationHash);
        if($conversation){
            $this->closeConversation($conversation);
        }

        return $this->getOrCreateSessionHashToActiveConversationByUserId(Auth::id());
    }
}
