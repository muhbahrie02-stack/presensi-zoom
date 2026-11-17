<?php

namespace App\Support;

/**
 * Link masuk Zoom yang membawa nama peserta, tanpa app Marketplace.
 * Parameter nama pada link ini tidak didokumentasikan resmi oleh Zoom:
 * uji ulang setiap kali Zoom diperbarui.
 */
class ZoomLink
{
    /** Memecah link undangan: https://telkomsel.zoom.us/j/99142396356?pwd=xxxx */
    public static function urai(string $link): ?array
    {
        if (! preg_match('~^https?://([a-z0-9.-]*zoom\.us)/(?:j|w|wc|s)/(\d{9,12})~i', trim($link), $m)) {
            return null;
        }
        parse_str((string) parse_url(trim($link), PHP_URL_QUERY), $q);

        return ['host' => strtolower($m[1]), 'meeting_number' => $m[2], 'passcode' => $q['pwd'] ?? null];
    }

    /** Membuka aplikasi Zoom. $skema: zoommtg (laptop) atau zoomus (HP). */
    public static function aplikasi(string $skema, string $host, string $mn, ?string $pwd, string $nama): string
    {
        return "$skema://$host/join?" . self::kueri(['action' => 'join', 'confno' => $mn, 'pwd' => $pwd, 'uname' => $nama]);
    }

    /** Membuka Zoom versi browser. Nama dikirim dalam dua bentuk yang dikenal web client. */
    public static function browser(string $host, string $mn, ?string $pwd, string $nama): string
    {
        return "https://$host/wc/$mn/join?" . self::kueri([
            'prefer' => 1, 'pwd' => $pwd, 'uname' => $nama, 'un' => base64_encode($nama),
        ]);
    }

    private static function kueri(array $p): string
    {
        return http_build_query(array_filter($p, fn ($v) => $v !== null && $v !== ''), '', '&', PHP_QUERY_RFC3986);
    }
}
