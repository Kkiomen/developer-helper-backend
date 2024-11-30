<?php

use App\Http\Controllers\CodeController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Broadcast;
use App\Http\Controllers\AssistantController;
use App\Http\Controllers\FileUploadController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');


Route::middleware('auth:sanctum')->post('/submit-code', [CodeController::class, 'submitCode']);
Broadcast::routes(['middleware' => ['auth:sanctum']]);

Route::group(['prefix'=>'assistant', 'middleware' => ['auth:sanctum']], function(){
    Route::post('/new-conversation/{conversationHash}', [AssistantController::class, 'newConversation']);
    Route::post('/send-message', [AssistantController::class, 'sendMessage']);
    Route::get('/session-hash', [AssistantController::class, 'getSessionHash']);
    Route::get('/messages/{conversationHash}', [AssistantController::class, 'getMessages']);


    Route::post('/upload-files', [FileUploadController::class, 'uploadFiles']);
});



//Route::middleware('auth:sanctum')->post('/broadcasting/auth', function (Request $request) {
//    Log::info('Broadcast auth request', ['user' => $request->user()]);
//    return response()->json(['authenticated' => Auth::check()]);
//});
