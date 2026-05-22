<?php
define('CLOUDINARY_CLOUD_NAME', 'dpu9o1cx2');
define('CLOUDINARY_API_KEY', '213157314922989');
define('CLOUDINARY_API_SECRET', 'Hg67jOdKF9Tnzy0yRf70axSPgv4');

function cloudinaryUpload(string $filePath): ?string {
    if (!file_exists($filePath)) return null;

    $timestamp = time();
    $signature = sha1("timestamp={$timestamp}" . CLOUDINARY_API_SECRET);

    $data = [
        'file' => 'data:' . mime_content_type($filePath) . ';base64,' . base64_encode(file_get_contents($filePath)),
        'api_key' => CLOUDINARY_API_KEY,
        'timestamp' => $timestamp,
        'signature' => $signature,
    ];

    $context = stream_context_create([
        'http' => [
            'method' => 'POST',
            'header' => "Content-Type: application/json\r\n",
            'content' => json_encode($data),
        ],
    ]);

    $response = file_get_contents("https://api.cloudinary.com/v1_1/" . CLOUDINARY_CLOUD_NAME . "/image/upload", false, $context);

    if ($response === false) return null;

    $result = json_decode($response, true);
    return $result['secure_url'] ?? null;
}

function cloudinaryDelete(string $url): void {
    $publicId = cloudinaryGetPublicId($url);
    if (!$publicId) return;

    $timestamp = time();
    $signature = sha1("public_id={$publicId}&timestamp={$timestamp}" . CLOUDINARY_API_SECRET);

    $data = [
        'public_id' => $publicId,
        'api_key' => CLOUDINARY_API_KEY,
        'timestamp' => $timestamp,
        'signature' => $signature,
    ];

    $context = stream_context_create([
        'http' => [
            'method' => 'POST',
            'header' => "Content-Type: application/json\r\n",
            'content' => json_encode($data),
        ],
    ]);

    file_get_contents("https://api.cloudinary.com/v1_1/" . CLOUDINARY_CLOUD_NAME . "/image/destroy", false, $context);
}

function cloudinaryGetPublicId(string $url): ?string {
    $parts = explode('/', $url);
    $file = end($parts);
    return pathinfo($file, PATHINFO_FILENAME);
}
