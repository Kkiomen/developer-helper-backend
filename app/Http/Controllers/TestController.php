<?php

namespace App\Http\Controllers;

use App\Api\QdrantHelper;
use App\Core\Assistant\Helper\KnowledgeHelper;
use App\Core\Assistant\Prompt\Abstract\Enum\OpenApiResultType;
use App\Core\Assistant\Prompt\ChooseKnowledgeFragmentPrompt;
use App\Core\Documentation\Service\DocumentationService;
use App\Events\MessageCode;
use App\Jobs\LoadKnowledgeCodeJob;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Pusher\Pusher;

class TestController extends Controller
{
    const FILE_KNOWLEDGE_PATH = '/assistant_memory_data/knowladge.json';

    public function test(QdrantHelper $qdrantd, DocumentationService $documentationService)
    {
//        dd($documentationService->parseAllFiles());



//        $query = 'Co mi możesz pomodzieć o module Payment?';
//        $collectionName = KnowledgeHelper::COLLECTION_CODE_NAME;
//
//        $qdrantResults = QdrantHelper::searchPoints($collectionName, $query);
//
//        $results = [];
//        $combinedResultString = '';
//        foreach ($qdrantResults as $index => $qdrantResult){
//            $currentElementResult ='<knowledge_element>';
//            $currentElementResult .='<index>' . $index . '</index>';
//            $currentElementResult .='<content>' . $qdrantResult['payload']['query'] ?? '' . '</content>';
//            $currentElementResult .='</knowledge_element>';
//
//            $combinedResultString .= $currentElementResult;
//            $results[$index] = $currentElementResult;
//        }
//
//        $userPrompt = 'Pytanie: ' . $query . '\n\n';
//        $userPrompt .= '### Baza wiedzy: \n' . $combinedResultString;
//
//        $chosenKnowledgeFragment = ChooseKnowledgeFragmentPrompt::generateContent(
//            userContent: $userPrompt,
//            resultType: OpenApiResultType::JSON_OBJECT
//        );
//
//        $chosenKnowledgeFragment = json_decode($chosenKnowledgeFragment, true)['selected_indices'];
//
//        $chosenKnowledge = array_filter($results, function ($key) use ($chosenKnowledgeFragment){
//            return in_array($key, $chosenKnowledgeFragment);
//        }, ARRAY_FILTER_USE_KEY);
//
//
//        dd($chosenKnowledge);







//        $path = public_path(self::FILE_KNOWLEDGE_PATH);
//        $jsonData = file_get_contents($path);
//        $dataArray = json_decode($jsonData, true);
//
//        foreach ($dataArray as $data){
//            LoadKnowledgeCodeJob::dispatch($data);
//        }
//
//        echo 'done';
//
//        dd($dataArray);

//        $path = public_path('/assistant_memory_data/knowladge.json');
//
//        dd(file_get_contents($path), file_exists($path));

//        $qdrantd->createCollection('documentation');
//
//        $qdrantd->addPoint(
//            collectionName: 'testowa',
//            id: 1,
//            query: '<code_element><file_path>\/home\/jowsianka\/Wise\/Checkout\/ApiUi\/Dto\/Checkout\/CartPaymentMethod.php<\/file_path> <description_class>Encja reprezentująca metodę płatności<\/description_class> \\n<uses><use>OpenApi\\Attributes<\/use><\/uses><class_name>CartPaymentMethod<\/class_name>\\n<method><method_name>setPriceNetFormatted<\/method_name><lines_in_code>124:129<\/lines_in_code><params><param><param_name>priceNetFormatted<\/param_name><param_type><\/param_type><implementation_param>    public function setPriceNetFormatted(?string $priceNetFormatted): self\n<\/implementation_param><\/param><\/params><\/method> \\n<\/code_element>',
//            payload: ['id' => 1, 'structure' => ['das' => 'dasdas', 'dasdas' => 'dasdasd']]
//        );
//
//
//        $r = $qdrantd->searchPoints('testowa', 'Encja metody płatności');
//        dd($r);
    }
}
