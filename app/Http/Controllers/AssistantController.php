<?php

namespace App\Http\Controllers;

use App\Core\Assistant\Facade\AssistantHandleMessageFacade;
use App\Core\Assistant\Service\ConversationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AssistantController extends Controller
{

    public function __construct(
        private ConversationService $conversationService
    ){}

    /**
     * Handle user message to Assistant
     * @param Request $request
     * @param AssistantHandleMessageFacade $assistantHandleMessageFacade
     * @return \Symfony\Component\HttpFoundation\StreamedResponse
     */
    public function sendMessage(Request $request, AssistantHandleMessageFacade $assistantHandleMessageFacade)
    {
        // Walidacja danych
        $request->validate([
            'message' => 'required|string',
            'session' => 'required|string',
            'type' => 'required|string',
        ]);

        // Załadowanie danych do fasady
        $assistantHandleMessageFacade->loadRequestData($request->all());

        // Obsługa wiadomości
        $assistantHandleMessageFacade->handleUserMessage();

        // Przygotowanie odpowiedzi
        return $assistantHandleMessageFacade->prepareResult();
    }


    /**
     * Get session hash for active conversation
     * @param Request $request
     * @return JsonResponse
     */
    public function getSessionHash(Request $request): JsonResponse
    {
        return response()->json([
            'hash' => $this->conversationService->getOrCreateSessionHashToActiveConversationByUserId(Auth::id())
        ]);
    }

    /**
     * Get messages by conversation hash
     * @param Request $request
     * @param string $conversationHash
     * @return JsonResponse
     */
    public function getMessages(Request $request, string $conversationHash): JsonResponse
    {
        return response()->json([
            'messages' => $this->conversationService->getConversationMessagesByHash($request->conversationHash)
        ]);
    }

    /**
     * Create new conversation
     * @param Request $request
     * @param string $conversationHash
     * @return JsonResponse
     */
    public function newConversation(Request $request, string $conversationHash): JsonResponse
    {
        return response()->json([
            'hash' => $this->conversationService->resetConversation($conversationHash)
        ]);
    }
}
