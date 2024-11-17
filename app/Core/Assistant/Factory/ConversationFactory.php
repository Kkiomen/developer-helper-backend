<?php

namespace App\Core\Assistant\Factory;

use App\Models\Conversation;
use Illuminate\Support\Facades\Auth;

/**
 * Class ConversationFactory - Factory for creating new conversation
 */
class ConversationFactory
{
    /**
     * Create new conversation
     * @param int|null $userId
     * @param bool $active
     * @return Conversation
     */
    public function createNewConversation(?int $userId = null, bool $active = true): Conversation
    {
        if(!$userId)
            $userId = Auth::id();

        $conversation = new Conversation();
        $conversation->user_id = $userId;
        $conversation->session_hash = $this->generateNewHash();
        $conversation->active = $active;
        $conversation->save();

        return $conversation;
    }

    /**
     * Generate new hash for conversation
     * @return string
     */
    public function generateNewHash(): string
    {
        do{
            $hash = md5(uniqid());
        }while(Conversation::where('session_hash', $hash)->exists());

        return $hash;
    }

}
