<?php

namespace App\Http\Controllers;

use App\Service\FileProjectService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class FileUploadController extends Controller
{
    public function uploadFiles(Request $request, FileProjectService $fileProjectService)
    {
        if ($request->hasFile('file')) {
            $file = $request->file('file');

            return $fileProjectService->saveAndExtractProjectFiles($file);
        } else {
            return response()->json(['message' => 'Nie otrzymano żadnego pliku.'], 400);
        }
    }
}
