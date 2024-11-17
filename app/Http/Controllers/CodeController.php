<?php

namespace App\Http\Controllers;

use App\Api\PusherEvent;
use App\Events\MessageCode;
use App\Events\TestEvent;
use App\Models\TmpDataModel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CodeController extends Controller
{
    public function submitCode(Request $request)
    {
        // Walidacja wejścia
        $validated = $request->validate([
            'code' => 'required|string',
        ]);

        $tmp = TmpDataModel::create([
            'data' => $validated['code']
        ]);

        PusherEvent::sendEvent('my-channel', 'my-event', ['user' => Auth::id(), 'code' => $validated['code']]);

        $tmp->save();

        // Zwrócenie odpowiedzi
//        return response()->json(['success' => 'true']);
        return response()->json(['success' => 'true', 'data' => $tmp]);
    }
}
