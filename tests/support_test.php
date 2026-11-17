<?php
// Uji logika murni tanpa Laravel: php tests/support_test.php
foreach (['NomorHp', 'NamaBersih', 'ZoomSignature', 'DurasiHadir'] as $c) {
    require __DIR__ . "/../app/Support/$c.php";
}
use App\Support\{NomorHp, NamaBersih, ZoomSignature, DurasiHadir};

$gagal = 0;
function cek(string $label, $dapat, $harap): void {
    global $gagal;
    $ok = $dapat === $harap;
    if (! $ok) $gagal++;
    echo ($ok ? 'OK   ' : 'GAGAL') . " $label" . ($ok ? '' : ' => ' . var_export($dapat, true) . ' (harap ' . var_export($harap, true) . ')') . "\n";
}

cek('hp 08',        NomorHp::normal('081234567890'), '081234567890');
cek('hp +62',       NomorHp::normal('+62 812-3456-7890'), '081234567890');
cek('hp 62',        NomorHp::normal('6281234567890'), '081234567890');
cek('hp 8',         NomorHp::normal('81234567890'), '081234567890');
cek('hp 0062',      NomorHp::normal('006281234567890'), '081234567890');
cek('hp pendek',    NomorHp::normal('0812345'), null);
cek('hp huruf',     NomorHp::normal('abc'), null);
cek('hp rumah',     NomorHp::normal('0411123456'), null);

cek('gelar koma',   NamaBersih::bersihkan('Fulan bin Fulan, S.H., M.H.'), 'Fulan bin Fulan');
cek('gelar depan',  NamaBersih::bersihkan('Dr. H. Ahmad Fulan'), 'Ahmad Fulan');
cek('gelar blkg',   NamaBersih::bersihkan('Ahmad Fulan Lc. M.A.'), 'Ahmad Fulan');
cek('gelar KM',     NamaBersih::bersihkan('KM. Amirullah, S.H., M.H.'), 'Amirullah');
cek('ust',          NamaBersih::bersihkan('  Ust.   Muhammad  Yusuf  '), 'Muhammad Yusuf');
cek('gelar HC',     NamaBersih::bersihkan('Dr. (H.C.) H. Nur Ihsan, Lc., M.Ag.'), 'Nur Ihsan');
cek('gelar drg',    NamaBersih::bersihkan('Prof. Dr. drg. H. Masriadi, S.K.G.'), 'Masriadi');
cek('nama polos',   NamaBersih::bersihkan('Muh Bahri'), 'Muh Bahri');
cek('nama = gelar', NamaBersih::bersihkan('Ma'), 'Ma');
cek('gelar K.H.',   NamaBersih::bersihkan('Dr. K.H. Muhammad Zaitun Rasmin, Lc., M.A.'), 'Muhammad Zaitun Rasmin');
cek('gelar (H.C.)', NamaBersih::bersihkan('Dr. (H.C.) H. Nur Ihsan Muh.Idris Lc., M.Ag.'), 'Nur Ihsan Muh.Idris');
cek('gelar nempel', NamaBersih::bersihkan('Elan Kurniawan.S.H., M.H.'), 'Elan Kurniawan');
cek('gelar Eng',    NamaBersih::bersihkan('Dr. Eng. Topan Setiadiputra, S.Si.'), 'Topan Setiadiputra');
cek('inisial',      NamaBersih::bersihkan('Ariesman M., S.T.P., M.Si.'), 'Ariesman M.');
cek('tak terlihat', NamaBersih::bersihkan("\u{2060}Asrianto Maddu Kelleng, S.S."), 'Asrianto Maddu Kelleng');
cek('tampilan',     NamaBersih::tampilan('000', 'Biro KKK', 'Ketua', 'Fulan'), '000.Biro KKK | Ketua. Fulan');

$jwt = ZoomSignature::buat('KEY', 'SECRET', '812 3456 7890', 0, 7200, 1_800_000_000);
[$h, $p, $s] = explode('.', $jwt);
$dec = fn ($x) => json_decode(base64_decode(strtr($x, '-_', '+/')), true);
cek('jwt header',   $dec($h), ['alg' => 'HS256', 'typ' => 'JWT']);
cek('jwt mn',       $dec($p)['mn'], '81234567890');
cek('jwt role',     $dec($p)['role'], 0);
cek('jwt exp',      $dec($p)['exp'] - $dec($p)['iat'], 7200);
cek('jwt tokenExp', $dec($p)['tokenExp'], $dec($p)['exp']);
cek('jwt tanda',    $s, rtrim(strtr(base64_encode(hash_hmac('sha256', "$h.$p", 'SECRET', true)), '+/', '-_'), '='));
cek('jwt maks 48j', (fn ($p) => $p['exp'] - $p['iat'])($dec(explode('.', ZoomSignature::buat('K', 'S', '1', 0, 999999))[1])), 172800);

cek('durasi normal',  DurasiHadir::tambah(100, 1000, 1030), 130);
cek('durasi jeda',    DurasiHadir::tambah(100, 1000, 1500), 100);
cek('durasi awal',    DurasiHadir::tambah(0, null, 1500), 0);
cek('hadir blm msk',  DurasiHadir::hadir(false, 9999, 3600, 0), false);
cek('hadir min 0',    DurasiHadir::hadir(true, 5, 3600, 0), true);
cek('hadir 75% krg',  DurasiHadir::hadir(true, 2699, 3600, 75), false);
cek('hadir 75% pas',  DurasiHadir::hadir(true, 2700, 3600, 75), true);

echo $gagal ? "\n$gagal uji GAGAL\n" : "\nSemua uji lulus\n";
exit($gagal ? 1 : 0);
