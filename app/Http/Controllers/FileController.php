<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;

class FileController extends Controller
{
    public function showUpload()
    {
        return view('files.upload');
    }

    // VULNERABLE A08: No file type validation, no integrity check
    // VULNERABLE A08: No virus scanning
    public function upload(Request $request)
    {
        $request->validate([
            'file' => 'required|file', // VULNERABLE A08: No mime type or extension validation
        ]);

        $file = $request->file('file');
        // VULNERABLE A08: Original filename used (path traversal possible)
        $path = $file->storeAs('uploads', $file->getClientOriginalName(), 'public');

        return response()->json([
            'message' => 'File berhasil diupload!',
            'path' => Storage::url($path),
            'filename' => $file->getClientOriginalName(),
        ]);
    }

    // VULNERABLE A10: SSRF - Server makes request to user-supplied URL
    public function fetchUrl(Request $request)
    {
        $url = $request->input('url');

        // VULNERABLE A10: No validation of URL, can access internal services
        // Can be used to scan internal network, access metadata endpoints, etc.
        try {
            $response = Http::timeout(10)->get($url);

            return response()->json([
                'status' => $response->status(),
                'headers' => $response->headers(),
                'body' => $response->body(),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    // VULNERABLE A10: SSRF via image proxy
    public function proxyImage(Request $request)
    {
        $imageUrl = $request->input('url');

        // VULNERABLE A10: No URL validation at all
        try {
            $response = Http::get($imageUrl);
            return response($response->body())
                ->header('Content-Type', $response->header('Content-Type'));
        } catch (\Exception $e) {
            return response('Image not found', 404);
        }
    }

    // VULNERABLE A03: Command injection via filename
    public function convertFile(Request $request)
    {
        $filename = $request->input('filename');

        // ✅ FIX: Validasi filename - hanya alfanumerik, titik, dan dash
        if (!preg_match('/^[a-zA-Z0-9._-]+$/', $filename)) {
            return response()->json(['error' => 'Nama file tidak valid'], 400);
        }

        // ✅ FIX: Pastikan file ada di direktori yang benar
        $path = storage_path('app/public/uploads/' . $filename);
        if (!file_exists($path)) {
            return response()->json(['error' => 'File tidak ditemukan'], 404);
        }

        // ✅ FIX: Gunakan PHP native function, bukan shell command
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mimeType = finfo_file($finfo, $path);
        $fileSize = filesize($path);
        finfo_close($finfo);

        return response()->json([
            'file_info' => [
                'name' => $filename,
                'mime' => $mimeType,
                'size' => $fileSize,
            ]
        ]);
    }


    // VULNERABLE A08: Insecure deserialization
    public function importData(Request $request)
    {
        $data = $request->input('data');

        // VULNERABLE A08: Unserializing user input
        try {
            $importedData = unserialize(base64_decode($data));
            return response()->json([
                'message' => 'Data berhasil diimport!',
                'data' => $importedData,
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Format data tidak valid'], 400);
        }
    }
}
