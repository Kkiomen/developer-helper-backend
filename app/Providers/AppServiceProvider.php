<?php

namespace App\Providers;

use App\Core\Assistant\Facade\AssistantHandleMessageFacade;
use App\Core\Assistant\Helper\ResponseHelper;
use App\Core\LLM\OpenApi\OpenApiLLMService;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
//        $this->app->singleton(AssistantHandleMessageFacade::class, function ($app) {
//            return new AssistantHandleMessageFacade(
//                $app->make(OpenApiLLMService::class),
//                $app->make(ResponseHelper::class)
//            );
//        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
