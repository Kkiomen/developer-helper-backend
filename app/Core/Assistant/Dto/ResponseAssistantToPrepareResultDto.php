<?php

namespace App\Core\Assistant\Dto;

class ResponseAssistantToPrepareResultDto
{
    private string $userMessage = '';
    private string $systemPrompt = '';

    private ?string $conversationHash = null;

    public function getUserMessage(): string
    {
        return $this->userMessage;
    }

    public function setUserMessage(string $userMessage): self
    {
        $this->userMessage = $userMessage;

        return $this;
    }

    public function getSystemPrompt(): string
    {
        return $this->systemPrompt;
    }

    public function setSystemPrompt(string $systemPrompt): self
    {
        $this->systemPrompt = $systemPrompt;

        return $this;
    }

    public function getConversationHash(): ?string
    {
        return $this->conversationHash;
    }

    public function setConversationHash(?string $conversationHash): self
    {
        $this->conversationHash = $conversationHash;

        return $this;
    }
}
