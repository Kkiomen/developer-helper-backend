<?php

namespace App\Core\Assistant\Helper;

use App\Api\QdrantHelper;
use App\Core\Assistant\Enum\KnowledgeType;
use App\Core\Assistant\Prompt\Abstract\Enum\OpenApiResultType;
use App\Core\Assistant\Prompt\ChooseKnowledgeFragmentPrompt;
use App\Models\Knowledge;

class KnowledgeHelper
{
    const COLLECTION_CODE_NAME = 'knowledge_code';

    public function __construct(
        private readonly QdrantHelper $qdrantHelper
    ){}


    /**
     * # Baza wiedzy
     * Zwraca najlepiej pasujące wyniki z bazy wiedzy
     * @param string $userMessage
     * @param string $collectionName
     * @return string|null
     */
    public static function getBestMatchingResultsFromKnowledge(string $userMessage, string $collectionName): ?string
    {
        $qdrantResults = QdrantHelper::searchPoints($collectionName, $userMessage);

        $results = [];
        $combinedResultString = '';
        foreach ($qdrantResults as $index => $qdrantResult){
            $currentElementResult ='<knowledge_element>';
            $currentElementResult .='<index>' . $index . '</index>';
            $currentElementResult .='<content>' . $qdrantResult['payload']['query'] ?? '' . '</content>';
            $currentElementResult .='</knowledge_element>';

            $combinedResultString .= $currentElementResult;
            $results[$index] = $currentElementResult;
        }

        $userPrompt = 'Pytanie: ' . $userMessage . '\n\n';
        $userPrompt .= '### Baza wiedzy: \n' . $combinedResultString;

        $chosenKnowledgeFragment = ChooseKnowledgeFragmentPrompt::generateContent(
            userContent: $userPrompt,
            resultType: OpenApiResultType::JSON_OBJECT
        );

        $chosenKnowledgeFragment = json_decode($chosenKnowledgeFragment, true)['selected_indices'];

        $chosenKnowledge = array_filter($results, function ($key) use ($chosenKnowledgeFragment){
            return in_array($key, $chosenKnowledgeFragment);
        }, ARRAY_FILTER_USE_KEY);


        return implode(' \n ', array_values($chosenKnowledge));
    }

    public function loadKnowledge(string $content): void
    {
        if(Knowledge::where('content', $content)->exists()){
            return;
        }

        $knowledge = Knowledge::create([
            'collection' => KnowledgeType::CODE->value,
            'content' => $content,
        ]);

        $this->qdrantHelper->addOrUpdatePoint(
            collectionName: KnowledgeType::CODE->value,
            id: $knowledge->id,
            query: $content,
        );
    }
}
