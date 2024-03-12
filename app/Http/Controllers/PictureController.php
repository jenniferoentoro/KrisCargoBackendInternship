<?php

namespace App\Http\Controllers;

use App\Helpers\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class PictureController extends Controller
{


    public function showFiles($filename)
    {
        $path = 'files/' . $filename;
        if (!Storage::exists($path)) {
            return ApiResponse::json(false, 'File tidak ditemukan', null, 404);
        }

        $file = Storage::get($path);
        $type = Storage::mimeType($path);

        return response($file)->header('Content-Type', $type);
    }
}
