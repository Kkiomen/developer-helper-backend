<?php

namespace App\Core\Assistant\Helper;

use App\Core\Assistant\Dto\ResponseAssistantToPrepareResultDto;
use App\Core\Assistant\Service\ConversationService;
use App\Core\LLM\OpenApi\OpenApiLLMService;
use App\Core\LLM\Utils\LanguageModelSettings;
use App\Core\LLM\Utils\LanguageModelType;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ResponseHelper
{
    public function __construct(
        private OpenApiLLMService $languageModel,
        private ConversationService $conversationService
    ){}

    public function responseStream(?ResponseAssistantToPrepareResultDto $responseDto): StreamedResponse
    {
        return response()->stream(function () use ($responseDto) {
            header('Content-Type: text/event-stream');
            header('Cache-Control: no-cache');
            header('Connection: keep-alive');

            $stream = $this->languageModel->generateStreamWithConversation(
                userMessage: $responseDto->getUserMessage(),
                systemPrompt: $responseDto->getSystemPrompt(),
                settings: (new LanguageModelSettings())->setLanguageModelType(LanguageModelType::NORMAL)->setTemperature(1),
                conversationMessages: $this->conversationService->getConversationMessagesByHash($responseDto->getConversationHash())
            );

            $result = '';
            foreach ($stream as $response) {
                $message = $response->choices[0]->toArray();
                if (!empty($message['delta']['content'])) {
                    $result .= $message['delta']['content'];
                }

//                if ($loggerStep !== null) {
//                    $message['steps'] = $loggerStep->getSteps();
//                }

                $message['table'] = $table ?? null;
                $message['type'] = $type ?? null;
                $message['data'] = $data ?? null;

                $this->sendSseMessage($message, 'message');
            }

            $this->conversationService->registerNewAiMessages(
                conversationHash: $responseDto->getConversationHash(),
                userMessage: $responseDto->getUserMessage(),
                systemPrompt: $responseDto->getSystemPrompt(),
                generatedContent: $result
            );

        });
    }

    /**
     * Sends a message via Server-Sent Events (SSE).
     * @param $data
     * @param $event
     * @return void
     */
    private function sendSseMessage($data, $event = null): void
    {
        if ($event) {
            echo "event: {$event}\n";
        }
        echo "data: " . json_encode($data) . "\n\n";
        ob_flush();
        flush();
    }
}
