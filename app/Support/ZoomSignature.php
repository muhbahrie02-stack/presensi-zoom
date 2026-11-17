<?php

namespace App\Support;

/**
 * JWT untuk Zoom Meeting SDK (HS256), dibuat di server.
 * Referensi: https://developers.zoom.us/docs/meeting-sdk/auth/
 */
class ZoomSignature
{
    public static function buat(
        string $sdkKey,
        string $sdkSecret,
        string $meetingNumber,
        int $role = 0,
        int $berlakuDetik = 7200,
        ?int $sekarang = null,
    ): string {
        // Zoom membatasi masa berlaku: minimal 30 menit, maksimal 48 jam.
        $berlakuDetik = max(1800, min($berlakuDetik, 48 * 3600));

        $iat = ($sekarang ?? time()) - 30;
        $exp = $iat + $berlakuDetik;

        $header = ['alg' => 'HS256', 'typ' => 'JWT'];
        $payload = [
            'appKey' => $sdkKey,
            'sdkKey' => $sdkKey,
            'mn' => preg_replace('/\D+/', '', $meetingNumber),
            'role' => $role,
            'iat' => $iat,
            'exp' => $exp,
            'tokenExp' => $exp,
        ];

        $isi = self::b64(json_encode($header)) . '.' . self::b64(json_encode($payload));

        return $isi . '.' . self::b64(hash_hmac('sha256', $isi, $sdkSecret, true));
    }

    private static function b64(string $data): string
    {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }
}
