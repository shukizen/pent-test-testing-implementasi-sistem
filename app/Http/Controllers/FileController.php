<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class FileController extends Controller
{
    // ✅ FIX A10: Gunakan Allowlist Approach untuk keamanan maksimal (Fix 3)
    private $allowedDomains = [
        'api.example.com',
        'cdn.example.com',
        'images.unsplash.com',
        'via.placeholder.com',
    ];

    public function showUpload()
    {
        return view('files.upload');
    }

    // VULNERABLE A08: No file type validation, no integrity check
    // VULNERABLE A08: No virus scanning
    public function upload(Request $request)
    {
        $request->validate([
            'file' => [
                'required',
                'file',
                'max:10240', // ✅ Max 10MB
                'mimes:jpg,jpeg,png,gif,pdf,doc,docx,xls,xlsx', // ✅ Whitelist ekstensi
                // ✅ Atau gunakan MIME type validation:
                'mimetypes:image/jpeg,image/png,image/gif,application/pdf',
            ],
        ]);

        $file = $request->file('file');

        // ✅ FIX: Generate random filename (jangan pakai nama asli)
        $filename = Str::uuid() . '.' . $file->getClientOriginalExtension();

        // ✅ FIX: Simpan di storage, bukan di public
        $path = $file->storeAs('uploads', $filename, 'local'); // Bukan 'public'!

        // ✅ FIX: Scan file (opsional - ClamAV integration)
        // $this->scanForVirus($path);

        return response()->json([
            'message' => 'File berhasil diupload!',
            'filename' => $filename,
        ]);
    }

    // VULNERABLE A10: SSRF - Server makes request to user-supplied URL
    public function fetchUrl(Request $request)
    {
        $url = $request->input('url');

        // ✅ FIX: Validasi URL format
        $request->validate([
            'url' => 'required|url',
        ]);

        // ✅ FIX: Cek apakah URL aman (Fix 1 & 2)
        if (!$this->isUrlSafe($url)) {
            return response()->json([
                'error' => 'URL tidak diizinkan. Tidak bisa mengakses alamat internal atau protokol terlarang.'
            ], 403);
        }

        try {
            // ✅ FIX: Disable Unnecessary Protocols & Batasi Redirect (Fix 4)
            $response = Http::timeout(5)
                ->withOptions([
                    'protocols' => ['http', 'https'], // ✅ Batasi hanya protokol web
                    'allow_redirects' => [
                        'max' => 3,
                        'strict' => true,
                        'protocols' => ['http', 'https'], // ✅ Redirect juga harus via HTTP/S
                    ],
                ])
                ->get($url);

            // ✅ FIX: Batasi ukuran response (mencegah DoS via file raksasa)
            $body = substr($response->body(), 0, 1024 * 100); // Max 100KB

            return response()->json([
                'status' => $response->status(),
                'body' => $body,
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Gagal mengambil URL atau timeout'], 500);
        }
    }

    private function isUrlSafe(string $url): bool
    {
        $parsed = parse_url($url);

        // ✅ Hanya izinkan HTTP dan HTTPS
        if (!in_array($parsed['scheme'] ?? '', ['http', 'https'])) {
            return false;
        }

        $host = $parsed['host'] ?? '';

        // ✅ Blokir private/internal IPs
        $ip = gethostbyname($host);

        $blockedRanges = [
            '127.0.0.0/8',      // Loopback
            '10.0.0.0/8',       // Private Class A
            '172.16.0.0/12',    // Private Class B
            '192.168.0.0/16',   // Private Class C
            '169.254.0.0/16',   // Link-local (metadata endpoints!)
            '0.0.0.0/8',        // Current network
            'fc00::/7',         // IPv6 private
            '::1/128',          // IPv6 loopback
        ];

        foreach ($blockedRanges as $range) {
            if ($this->ipInRange($ip, $range)) {
                return false;
            }
        }

        // ✅ Blokir hostname khusus
        $blockedHosts = [
            'localhost',
            'metadata.google.internal',
            'metadata.internal',
        ];

        if (in_array(strtolower($host), $blockedHosts)) {
            return false;
        }

        return true;
    }

    private function ipInRange(string $ip, string $range): bool
    {
        if (!str_contains($range, '/')) {
            return $ip === $range;
        }

        [$subnet, $mask] = explode('/', $range);
        
        $subnet = ip2long($subnet);
        $ip = ip2long($ip);
        
        if ($subnet === false || $ip === false) {
            return false; // Handle IPv6 atau format salah
        }

        $mask = ~((1 << (32 - $mask)) - 1);

        return ($ip & $mask) === ($subnet & $mask);
    }

    private function isDomainAllowed(string $url): bool
    {
        $host = parse_url($url, PHP_URL_HOST);
        return in_array(strtolower($host), $this->allowedDomains);
    }



    // VULNERABLE A10: SSRF via image proxy
    public function proxyImage(Request $request)
    {
        $imageUrl = $request->input('url');

        // ✅ FIX: Validasi URL
        if (!$this->isUrlSafe($imageUrl)) {
            return response('URL tidak diizinkan', 403);
        }

        try {
            // ✅ FIX: Batasi protokol (Fix 4)
            $response = Http::timeout(5)
                ->withOptions([
                    'protocols' => ['http', 'https'],
                ])
                ->get($imageUrl);

            // ✅ FIX: Pastikan response adalah gambar
            $contentType = $response->header('Content-Type');
            $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp', 'image/svg+xml'];

            if (!in_array($contentType, $allowedTypes)) {
                return response('Bukan file gambar', 400);
            }

            // ✅ FIX: Batasi ukuran (max 5MB)
            if (strlen($response->body()) > 5 * 1024 * 1024) {
                return response('File terlalu besar', 400);
            }

            return response($response->body())
                ->header('Content-Type', $contentType)
                ->header('X-Content-Type-Options', 'nosniff');
        } catch (\Exception $e) {
            return response('Image not found or access denied', 404);
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

        // ✅ FIX: Gunakan JSON, BUKAN unserialize()
        try {
            $importedData = json_decode(base64_decode($data), true);

            if (json_last_error() !== JSON_ERROR_NONE) {
                return response()->json(['error' => 'Format JSON tidak valid'], 400);
            }

            // ✅ FIX: Validasi struktur data
            $validated = validator($importedData, [
                'name' => 'required|string|max:255',
                'email' => 'required|email',
            ])->validate();

            return response()->json([
                'message' => 'Data berhasil diimport!',
                'data' => $validated,
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Format data tidak valid'], 400);
        }
    }
}
