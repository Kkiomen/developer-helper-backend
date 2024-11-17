<?php

namespace App\Core\LLM\OpenApi;

use App\Core\LLM\Utils\LanguageModelSettings;
use App\Core\LLM\Utils\LanguageModelType;
use OpenAI\Client;

class OpenApiLLMService
{
    private Client $client;

    public function __construct(){
        $this->client = static::getClient();
    }

    public function generateStreamWithConversation(string $userMessage, string $systemPrompt, LanguageModelSettings $settings, ?array $conversationMessages = null): mixed
    {
        $messages = [];
        $openAiModel = $this->getOpenAiModelBySettings($settings);

        // ============ Prepare Messages ============
        $messages[] = ['role' => 'system', 'content' => $systemPrompt];

        if($conversationMessages !== null){
            foreach ($conversationMessages as $message){
                if(!empty($message['message'])){
                    $messages[] = ['role' => $message['type'], 'content' => $message['message']];
                }
            }
        }

        $messages[] = ['role' => 'user', 'content' => $userMessage];
        // ============ Prepare Messages ============

        $openAiModelParamsToGenerate = [
            'temperature' => $settings->getTemperature(),
            'model' => $openAiModel->value,
            'messages' => $messages
        ];

        return $this->client->chat()->createStreamed($openAiModelParamsToGenerate);
    }

    public function embeddedString(string $content): mixed
    {
        $response = $this->client->embeddings()->create([
            'model' => OpenAiModel::TEXT_EMBEDDING_ADA->value,
            'input' => $content
        ]);

        foreach ($response->embeddings as $embedding){
            return $embedding->embedding;
        }

        return null;
    }



    protected function getOpenAiModelBySettings(LanguageModelSettings $languageModelSettings): OpenAiModel
    {
        /** @var OpenAiModel $model */
        $model = match ($languageModelSettings->getLanguageModelType()) {
            LanguageModelType::NORMAL => OpenAiModel::GPT_4_O_MINI,
            LanguageModelType::INTELLIGENT => OpenAiModel::GPT_4_O
        };

        return $model;
    }


    /**
     * Zwraca klienta openai
     * @return Client
     */
    public static function getClient(): Client
    {
        return \OpenAI::client(getenv('OPEN_AI_KEY'));
    }
}
