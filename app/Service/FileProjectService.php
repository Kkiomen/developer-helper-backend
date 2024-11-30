<?php

namespace App\Service;

use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\File;

class FileProjectService
{
    public static function saveAndExtractProjectFiles(UploadedFile $file): JsonResponse
    {
        static::removeOldProjectFiles();

        // Zapisanie pliku ZIP
        $path = $file->store('archives');

        // Pobranie nazwy projektu
        $pathExplode = explode('/', $path);
        $userProjectPath = str_replace('.zip', '', end($pathExplode));

        // Ustawienie uprawnień
        $fullPath = storage_path('app/private/' . $path);
        chmod($fullPath, 0777);


        Log::info('File stored at path: ' . $path);
        $user = User::find(Auth::id());
        $user->project_path = $userProjectPath;
        $user->save();

        // Opcjonalnie rozpakuj archiwum
        $zip = new \ZipArchive;
        if ($zip->open($fullPath) === TRUE) {
            $zip->extractTo(storage_path('app/private/unpacked/' . $userProjectPath . '/'));
            $zip->close();
            return response()->json(['message' => 'Plik został przesłany i rozpakowany!'], 200);
        } else {
            return response()->json(['message' => 'Błąd podczas rozpakowywania archiwum.'], 500);
        }
    }

    protected static function removeOldProjectFiles(): void
    {
        $user = User::find(Auth::id());
        $userProjectPath = $user->project_path;

        if(empty($userProjectPath)){
            return;
        }

        $fullPath = storage_path('app/private/archives/' . $userProjectPath . '.zip');
        File::delete($fullPath);

        $fullPath = storage_path('app/private/unpacked/' . $userProjectPath);
        File::deleteDirectory($fullPath);
    }
}
