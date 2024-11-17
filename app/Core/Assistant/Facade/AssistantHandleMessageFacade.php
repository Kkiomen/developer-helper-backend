<?php

namespace App\Core\Assistant\Facade;

use App\Core\Assistant\Dto\AssistantHandleMessageDto;
use App\Core\Assistant\Dto\ResponseAssistantToPrepareResultDto;
use App\Core\Assistant\Enum\KnowledgeType;
use App\Core\Assistant\Helper\KnowledgeHelper;
use App\Core\Assistant\Helper\ResponseHelper;
use App\Core\Assistant\Prompt\DefaultAssistantPrompt;
use App\Core\LLM\OpenApi\OpenApiLLMService;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AssistantHandleMessageFacade
{
    private ?AssistantHandleMessageDto $assistantHandleMessageDto = null;

    // Na podstawie tego DTO zostanie przygotowana odpowiedź
    private ?ResponseAssistantToPrepareResultDto $responseAssistantToPrepareResultDto = null;

    public function __construct(
        private OpenApiLLMService $openApiLLMService,
        private ResponseHelper $responseHelper
    ){}

    public function handleUserMessage(): void
    {
        $knowledgeCode = KnowledgeHelper::getBestMatchingResultsFromKnowledge($this->assistantHandleMessageDto->getUserMessage(), KnowledgeType::CODE->value);
        $knowledgeDocumentation = KnowledgeHelper::getBestMatchingResultsFromKnowledge($this->assistantHandleMessageDto->getUserMessage(), KnowledgeType::DOCUMENTATION->value);


        // Dodanie system prompt
        $this->assistantHandleMessageDto->setSystemPrompt(DefaultAssistantPrompt::getPrompt() . ' Nie halucynuj! ### Baza wiedzy z kodu: \n' . $knowledgeCode . ' \n\n ===== Baza wiedzy z dokumentacji ==== \n\n' . $knowledgeDocumentation);

        // Przygotowanie dto na podstawie którego zostanie przygotowana odpowiedź
        $response = new ResponseAssistantToPrepareResultDto();
        $response
            ->setUserMessage($this->assistantHandleMessageDto->getUserMessage())
            ->setSystemPrompt($this->assistantHandleMessageDto->getSystemPrompt())
            ->setConversationHash($this->assistantHandleMessageDto->getSession());

        $this->responseAssistantToPrepareResultDto = $response;
    }

    public function loadRequestData(array $requestData): void
    {
        $assistantHandleMessageDto = new AssistantHandleMessageDto();
        $assistantHandleMessageDto
            ->setUserMessage($requestData['message'] ?? null)
            ->setSession($requestData['session'] ?? null)
            ->setTypeAssistant($requestData['type'] ?? null);

        $this->assistantHandleMessageDto = $assistantHandleMessageDto;
    }

    /**
     * Przygotowanie odpowiedzi
     * @return StreamedResponse
     */
    public function prepareResult(): StreamedResponse
    {
        return $this->responseHelper->responseStream($this->responseAssistantToPrepareResultDto);
    }
}
