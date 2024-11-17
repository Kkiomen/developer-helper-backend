<?php

namespace App\Jobs;

use App\Core\Assistant\Helper\KnowledgeHelper;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class LoadKnowledgeCodeJob implements ShouldQueue
{
    use Queueable;

    private string $content;

    /**
     * Create a new job instance.
     */
    public function __construct(string $content)
    {
        $this->content = $content;
    }

    /**
     * Execute the job.
     */
    public function handle(KnowledgeHelper $knowledgeHelper): void
    {
        $knowledgeHelper->loadKnowledge($this->content);
    }
}
