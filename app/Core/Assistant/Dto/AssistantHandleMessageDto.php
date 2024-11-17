<?php

namespace App\Core\Assistant\Dto;

class AssistantHandleMessageDto
{
    private ?string $userMessage = null;
    private ?string $session = null;
    private ?string $typeAssistant = null;
    private ?string $systemPrompt = null;

    public function getUserMessage(): ?string
    {
        return $this->userMessage;
    }

    public function setUserMessage(?string $userMessage): self
    {
        $this->userMessage = $userMessage;

        return $this;
    }

    public function getSession(): ?string
    {
        return $this->session;
    }

    public function setSession(?string $session): self
    {
        $this->session = $session;

        return $this;
    }

    public function getTypeAssistant(): ?string
    {
        return $this->typeAssistant;
    }

    public function setTypeAssistant(?string $typeAssistant): self
    {
        $this->typeAssistant = $typeAssistant;

        return $this;
    }

    public function getSystemPrompt(): ?string
    {
        return $this->systemPrompt;
    }

    public function setSystemPrompt(?string $systemPrompt): self
    {
        $this->systemPrompt = $systemPrompt;

        return $this;
    }
}
