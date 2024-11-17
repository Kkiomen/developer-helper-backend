<?php

namespace App\Console\Commands;

use App\Api\QdrantHelper;
use App\Core\Assistant\Helper\KnowledgeHelper;
use App\Jobs\LoadKnowledgeCodeJob;
use App\Models\Knowledge;
use Illuminate\Console\Command;

class LoadKnowledgeCodeCommand extends Command
{
    const FILE_KNOWLEDGE_PATH = '/assistant_memory_data/knowladge.json';

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:load-knowledge-code';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Ładowanie danych do bazy wiedzy';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $path = public_path(self::FILE_KNOWLEDGE_PATH);
        $jsonData = file_get_contents($path);
        $dataArray = json_decode($jsonData, true);

        $bar = $this->output->createProgressBar(count($dataArray));

        foreach ($dataArray as $data) {
            LoadKnowledgeCodeJob::dispatch($data);
            $bar->advance();
        }

        $bar->finish();
    }
}
