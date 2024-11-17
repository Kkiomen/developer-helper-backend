<?php

namespace App\Api;

use App\Core\LLM\OpenApi\OpenApiLLMService;
use InvalidArgumentException;
use Qdrant\Config;
use Qdrant\Http\Builder;
use Qdrant\Models\PointsStruct;
use Qdrant\Models\PointStruct;
use Qdrant\Models\Request\SearchRequest;
use Qdrant\Models\VectorStruct;
use Qdrant\Qdrant;
use Qdrant\Models\Request\CreateCollection;
use Qdrant\Models\Request\VectorParams;

/**
 * # Klasa pomocnicza obsługująca Qdrant
 * $qdrantd->createCollection('testowa');
 *
 * $qdrantd->addPoint(
 *   collectionName: 'testowa',
 *   id: 1,
 *   query: '<code_element><file_path>\/home\/jowsianka\/Wise\/Checkout\/ApiUi\/Dto\/Checkout\/CartPaymentMethod.php<\/file_path> <description_class>Encja reprezentująca metodę płatności<\/description_class> \\n<uses><use>OpenApi\\Attributes<\/use><\/uses><class_name>CartPaymentMethod<\/class_name>\\n<method><method_name>setPriceNetFormatted<\/method_name><lines_in_code>124:129<\/lines_in_code><params><param><param_name>priceNetFormatted<\/param_name><param_type><\/param_type><implementation_param>    public function setPriceNetFormatted(?string $priceNetFormatted): self\n<\/implementation_param><\/param><\/params><\/method> \\n<\/code_element>',
 *   payload: ['id' => 1, 'structure' => ['das' => 'dasdas', 'dasdas' => 'dasdasd']]
 * );
 *
 *
 * $r = $qdrantd->searchPoints('testowa', 'Encja metody płatności');
 * dd($r);
 */
class QdrantHelper
{
    private Qdrant $client;


    public function __construct(
        private OpenApiLLMService $openApiLLMService
    ){
        $this->client = $this->init();
    }

    /**
     * # Kolekcja
     * Dodawanie nowej kolekcji
     * @param string $collectionName
     * @param string $distanceCalc
     * @return void
     */
    public function createCollection(string $collectionName, string $distanceCalc = VectorParams::DISTANCE_COSINE): bool
    {
        if(!in_array($distanceCalc, [VectorParams::DISTANCE_COSINE, VectorParams::DISTANCE_DOT, VectorParams::DISTANCE_EUCLID])) {
            throw new InvalidArgumentException('Invalid distance for Vector Param');
        }

        $createCollection = new CreateCollection();
        $createCollection->addVector(new VectorParams(3072, $distanceCalc), 'content');
        $response = $this->client->collections($collectionName)->create($createCollection);

        return $response->__toArray()['result'];
    }

    public function addOrUpdatePoint(string $collectionName, int $id, string $query, array $payload = []): bool
    {
        $response = OpenApiLLMService::getClient()->embeddings()->create([
            'model' => 'text-embedding-3-large',
            'input' => $query,
        ]);
        $embedding = array_values($response->embeddings[0]->embedding);

        $payload['query'] = $query;

        $points = new PointsStruct();
        $points->addPoint(
            new PointStruct(
                $id,
                new VectorStruct($embedding, 'content'),
                $payload
            )
        );
        $response = $this->client->collections($collectionName)->points()->upsert($points);

        return $response->__toArray()['status'] == 'ok';
    }

    public function removePoints(string $collectionName, array $ids): bool
    {
        $response = $this->client->collections($collectionName)->points()->delete($ids);

        return $response->__toArray()['status'] == 'ok';
    }

    /**
     * # Wyszukiwanie punktów
     * Wyszukiwanie punktów w kolekcji
     * @param string $collectionName
     * @param string $query
     * @return array
     */
    public static function searchPoints(string $collectionName, string $query): array
    {
        $response = OpenApiLLMService::getClient()->embeddings()->create([
            'model' => 'text-embedding-3-large',
            'input' => $query,
        ]);
        $embedding = array_values($response->embeddings[0]->embedding);

        $searchRequest = (new SearchRequest(new VectorStruct($embedding, 'content')))
            ->setLimit(15)
            ->setWithPayload(true);

        $client = static::init();
        $response = $client->collections($collectionName)->points()->search($searchRequest);

        return $response['result'];
    }

    /**
     * Inicjalizacja klienta
     * @return void
     */
    private static function init(): Qdrant
    {
        $config = new Config($_ENV['QDRANT_HOST'], $_ENV['QDRANT_PORT']);
        $transport = (new Builder())->build($config);

        return new Qdrant($transport);
    }




}
