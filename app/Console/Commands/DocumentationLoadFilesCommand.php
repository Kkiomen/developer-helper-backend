<?php

namespace App\Console\Commands;

use App\Api\QdrantHelper;
use App\Core\Assistant\Enum\KnowledgeType;
use App\Core\Documentation\Service\DocumentationService;
use App\Core\LLM\OpenApi\OpenApiLLMService;
use App\Models\DocumentationFile;
use App\Models\DocumentationFileContent;
use App\Models\Knowledge;
use Illuminate\Console\Command;

class DocumentationLoadFilesCommand extends Command
{
    private ?QdrantHelper $qdrantHelper = null;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:documentation-load-files-command';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Ładowanie dokumentacji z projektu Docosaurusa (pliki md i mdx)';

    /**
     * Execute the console command.
     */
    public function handle(DocumentationService $documentationService, QdrantHelper $qdrantHelper)
    {
        $this->qdrantHelper = $qdrantHelper;

        $preparedDocumentationFiles = $documentationService->parseAllFiles();
        $actualDocumentationFileContent = DocumentationFileContent::all()->toArray();
        $documentationFiles = DocumentationFile::all()->toArray();

        $this->output->progressStart($this->countParseContent($preparedDocumentationFiles));

        $this->processDocumentationFiles($preparedDocumentationFiles, $actualDocumentationFileContent, $documentationFiles);
        $this->deleteUnusedDocFiles($documentationFiles);
        $this->deleteUnusedKnowledgeBases($actualDocumentationFileContent);

        $this->output->progressFinish();
    }

    /**
     * Count the parse content.
     * @param array $preparedDocumentationFiles
     * @return int
     */
    protected function countParseContent(array $preparedDocumentationFiles): int
    {
        $count = 0;
        foreach ($preparedDocumentationFiles as $file) {
            $count += count($file['parseContent']);
        }
        return $count;
    }

    /**
     * Process the parsed documentation files.
     *
     * @param array $preparedDocumentationFiles
     * @param array $currentKnowledgeBases
     * @param array $documentationFiles
     * @return void
     */
    protected function processDocumentationFiles(array $preparedDocumentationFiles, array &$currentKnowledgeBases, array &$documentationFiles): void
    {
        foreach ($preparedDocumentationFiles as $file) {
            $documentationFile = $this->getDocumentationFileIfExists($file['dir']);
            if ($documentationFile) {
                $this->updateDocumentationFile($documentationFile, $file);
                $this->removeProcessedDocFile($documentationFiles, $file['dir']);
            } else {
                $this->createDocumentationFile($file);
            }

            $this->processDocumentationFileContent($file, $currentKnowledgeBases);
        }
    }

    /**
     * Update the DocFile if it exists.
     *
     * @param DocumentationFile $documentationFile
     * @param array $file
     * @return void
     */
    protected function updateDocumentationFile(DocumentationFile $documentationFile, array $file): void
    {
        if ($documentationFile->md5 !== $file['md5']) {
            $documentationFile->md5 = $file['md5'];
            $documentationFile->save();
        }
    }
    /**
     * Remove the processed DocFile from the list.
     *
     * @param array $docFiles
     * @param string $filename
     * @return void
     */
    protected function removeProcessedDocFile(array &$docFiles, string $filename): void
    {
        foreach ($docFiles as $key => $currentDocFile) {
            if ($currentDocFile['filename'] === $filename) {
                unset($docFiles[$key]);
            }
        }
    }
    /**
     * Create a new DocFile.
     *
     * @param array $file
     * @return void
     */
    protected function createDocumentationFile(array $file): void
    {
        $documentationFile = new DocumentationFile();
        $documentationFile->filename = $file['dir'];
        $documentationFile->md5 = $file['md5'];
        $documentationFile->save();
    }

    /**
     * Process the knowledge base content.
     *
     * @param array $file
     * @param array $currentKnowledgeBases
     * @return void
     */
    protected function processDocumentationFileContent(array $file, array &$currentKnowledgeBases): void
    {
        if (!empty($file['parseContent'])) {
            foreach ($file['parseContent'] as $content) {

                if ($this->shouldSkipContent($content['content'], $content['header'])) {
                    continue;
                }

                $documentationFileContent = DocumentationFileContent::where('filename', $file['dir'])
                    ->where('header', $content['header'])
                    ->first();

                if ($documentationFileContent) {
                    $this->updateKnowledgeBase($documentationFileContent, $content);
                } else {
                    $this->createDocumentationFileContent($file, $content);
                }

                $this->removeProcessedKnowledgeBase($currentKnowledgeBases, $file['dir'], $content['header']);
                $this->output->progressAdvance();
            }
        }
    }
    /**
     * Determine if the content should be skipped.
     *
     * @param string $content
     * @param string $header
     * @return bool
     */
    protected function shouldSkipContent(string $content, string $header): bool
    {
        if(strlen($content) <= strlen($header) + 50) {
            return true;
        }
        return str_contains($content, 'sidebar_position') || str_contains($content, 'Markdown text with');
    }

    /**
     * Update the existing KnowledgeBase.
     *
     * @param DocumentationFileContent $documentationFileContent
     * @param array $content
     * @return void
     */
    protected function updateKnowledgeBase(DocumentationFileContent $documentationFileContent, array $content): void
    {
        $md5CurrentContent = md5($content['content']);
        if ($documentationFileContent->md5 !== $md5CurrentContent) {
            $currentContent = $content['header'] . '\n' .$content['content'];
            $documentationFileContent->md5 = $md5CurrentContent;
            $documentationFileContent->parse_content = $this->prepareContentForPrompt($currentContent);
            $documentationFileContent->header = $this->prepareHeader($content['header']);
            $documentationFileContent->embedded = $this->prepareEmbedded($currentContent);
            $documentationFileContent->save();

            $knowledge = Knowledge::where('knowledge_id', $documentationFileContent->id)
                ->where('collection', KnowledgeType::DOCUMENTATION->value)
                ->first();

            if ($knowledge) {
                $this->qdrantHelper->addOrUpdatePoint($knowledge->collection, $knowledge->id, $this->prepareContentForPrompt($currentContent), ['knowledge_id' => $knowledge->knowledge_id]);
            }
        }
    }

    /**
     * Create a new KnowledgeBase.
     *
     * @param array $file
     * @param array $content
     * @return void
     */
    protected function createDocumentationFileContent(array $file, array $content): void
    {
        $currentContent = $content['header'] . '\n' .$content['content'];
        $documentationFileContent = new DocumentationFileContent();
        $documentationFileContent->filename = $file['dir'];
        $documentationFileContent->md5 = md5($content['content']);
        $documentationFileContent->embedded = $this->prepareEmbedded($currentContent);
        $documentationFileContent->header = $this->prepareHeader($content['header']);
        $documentationFileContent->parse_content = $this->prepareContentForPrompt($currentContent);
        $documentationFileContent->save();


        $knowledge = new Knowledge();
        $knowledge->collection = KnowledgeType::DOCUMENTATION->value;
        $knowledge->content = $documentationFileContent->parse_content;
        $knowledge->knowledge_id = $documentationFileContent->id;
        $knowledge->save();

        $this->qdrantHelper->addOrUpdatePoint($knowledge->collection, $knowledge->id, $knowledge->content, ['knowledge_id' => $knowledge->knowledge_id]);
    }
    /**
     * Remove the processed KnowledgeBase from the list.
     *
     * @param array $currentKnowledgeBases
     * @param string $filename
     * @param string $header
     * @return void
     */
    protected function removeProcessedKnowledgeBase(array &$currentKnowledgeBases, string $filename, string $header): void
    {
        foreach ($currentKnowledgeBases as $key => $currentKnowledgeBase) {
            if ($currentKnowledgeBase['filename'] === $filename && $currentKnowledgeBase['header'] === $header) {
                unset($currentKnowledgeBases[$key]);
            }
        }
    }
    /**
     * Delete unused DocFiles.
     *
     * @param array $docFiles
     * @return void
     */
    protected function deleteUnusedDocFiles(array $docFiles): void
    {
        foreach ($docFiles as $docFile) {
            $file = DocumentationFile::find($docFile['id']);
            if ($file) {
                $file->delete();
            }
        }
    }
    /**
     * Delete unused KnowledgeBases.
     *
     * @param array $currentKnowledgeBases
     * @return void
     */
    protected function deleteUnusedKnowledgeBases(array $currentKnowledgeBases): void
    {
        foreach ($currentKnowledgeBases as $knowledgeBase) {
            $file = DocumentationFileContent::find($knowledgeBase['id']);
            if ($file) {
                $knowledge = Knowledge::where('knowledge_id', $file->id)
                    ->where('collection', KnowledgeType::DOCUMENTATION->value)
                    ->first();

                $this->qdrantHelper->removePoints(KnowledgeType::DOCUMENTATION->value, [$knowledge->id]);


                $file->delete();
            }
        }
    }

    /**
     * Prepare embedded content.
     *
     * @param string $content
     * @return array
     */
    protected function prepareEmbedded(string $content): array
    {
        $response = OpenApiLLMService::getClient()->embeddings()->create([
            'model' => 'text-embedding-3-large',
            'input' => $content,
        ]);

        return array_values($response->embeddings[0]->embedding);
    }

    /**
     * Get the DocFile if it exists.
     *
     * @param string $filename
     * @return DocumentationFile|null
     */
    protected function getDocumentationFileIfExists(string $filename): ?DocumentationFile
    {
        return DocumentationFile::where('filename', $filename)->first();
    }

    /**
     * Prepare the header.
     * @param string $header
     * @return string
     */
    protected function prepareHeader(string $header): string
    {
        $maxLength = 244;
        if (strlen($header) > $maxLength) {
            $header = substr($header, -$maxLength);
        }
        return $header;
    }

    /**
     * Prepare the content for the prompt.
     * @param string $content
     * @param int $maxLength
     * @return string
     */
    function prepareContentForPrompt(string $content, int $maxLength = 4000): string {
        // Usuń nadmiarowe spacje i entery
        $content = preg_replace('/\s+/', ' ', $content);
        $content = trim($content);
        // Skróć string do zadanej długości, nie usuwając ważnej zawartości
        if (strlen($content) > $maxLength) {
            $content = substr($content, 0, $maxLength);
            // Znajdź ostatnie wystąpienie pełnego zdania, aby nie przerwać treści
            $lastSentenceEnd = max(strrpos($content, '.'), strrpos($content, '!'), strrpos($content, '?'));
            if ($lastSentenceEnd !== false) {
                $content = substr($content, 0, $lastSentenceEnd + 1);
            } else {
                // Jeśli nie znaleziono końca zdania, ucinamy w najbliższym sensownym miejscu
                $content = substr($content, 0, strrpos($content, ' ')) . '...';
            }
        }
        return $content;
    }
}
