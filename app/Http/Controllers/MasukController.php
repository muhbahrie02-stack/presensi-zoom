<?php

namespace App\Http\Controllers;

use App\Models\Bidang;
use App\Models\Jabatan;
use App\Models\Kehadiran;
use App\Models\Peserta;
use App\Models\Rapat;
use App\Support\DurasiHadir;
use App\Support\NamaBersih;
use App\Support\NomorHp;
use App\Support\ZoomLink;
use App\Support\ZoomSignature;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class MasukController extends Controller
{
    /** Langkah 1: form nomor HP. */
    public function form(Rapat $rapat)
    {
        return view('masuk', compact('rapat'));
    }

    /** Langkah 1: cek nomor HP. Terdaftar -> ruang rapat; belum -> form pendaftaran. */
    public function cek(Request $request, Rapat $rapat)
    {
        abort_unless($rapat->aktif, 403, 'Rapat ini tidak aktif.');

        $noHp = NomorHp::normal($request->input('no_hp'));
        if (! $noHp) {
            return back()->withErrors(['no_hp' => 'Nomor HP tidak valid. Contoh: 081234567890'])->withInput();
        }

        $peserta = Peserta::where('no_hp', $noHp)->first();
        if (! $peserta) {
            $request->session()->put('daftar_no_hp', $noHp);

            return redirect()->route('daftar', $rapat);
        }

        return $this->mulaiSesi($request, $rapat, $peserta);
    }

    /** Langkah 2: form pendaftaran mandiri. */
    public function daftarForm(Request $request, Rapat $rapat)
    {
        $noHp = $request->session()->get('daftar_no_hp');
        if (! $noHp) {
            return redirect()->route('masuk', $rapat);
        }

        return view('daftar', [
            'rapat' => $rapat,
            'noHp' => $noHp,
            'bidangs' => Bidang::orderBy('kode')->get(),
            'jabatans' => Jabatan::orderBy('urutan')->orderBy('nama')->get(),
        ]);
    }

    /** Langkah 2: simpan pendaftaran, lalu langsung lanjut seperti langkah 1. */
    public function daftarSimpan(Request $request, Rapat $rapat)
    {
        abort_unless($rapat->aktif, 403, 'Rapat ini tidak aktif.');

        $noHp = $request->session()->get('daftar_no_hp');
        if (! $noHp) {
            return redirect()->route('masuk', $rapat);
        }

        $data = $request->validate([
            'nama' => ['required', 'string', 'min:2', 'max:60'],
            'bidang_id' => ['required', 'exists:bidangs,id'],
            'jabatan_id' => ['required', 'exists:jabatans,id'],
        ], [], ['nama' => 'nama', 'bidang_id' => 'bidang kerja', 'jabatan_id' => 'jabatan']);

        $nama = NamaBersih::bersihkan($data['nama']);
        if (mb_strlen($nama) < 2) {
            return back()->withErrors(['nama' => 'Tulis nama tanpa gelar.'])->withInput();
        }

        // firstOrCreate: aman bila form terkirim dua kali
        $peserta = Peserta::firstOrCreate(['no_hp' => $noHp], [
            'nama' => $nama,
            'bidang_id' => $data['bidang_id'],
            'jabatan_id' => $data['jabatan_id'],
            'terverifikasi' => false,
            'sumber' => 'mandiri',
        ]);
        $request->session()->forget('daftar_no_hp');

        return $this->mulaiSesi($request, $rapat, $peserta);
    }

    /** Mencatat klik dan membuat sesi baru. Sesi lama untuk nomor yang sama gugur. */
    private function mulaiSesi(Request $request, Rapat $rapat, Peserta $peserta)
    {
        $token = Str::random(48);
        $nama = $peserta->namaTampilan();

        $hadir = Kehadiran::firstOrNew(['rapat_id' => $rapat->id, 'peserta_id' => $peserta->id]);
        $hadir->fill(['nama_tampilan' => $nama, 'token' => $token]);
        $hadir->klik_at ??= now();
        $hadir->save();

        $request->session()->put("hadir.{$rapat->id}", ['id' => $hadir->id, 'token' => $token]);

        return redirect()->route('ruang', $rapat);
    }

    /** Halaman Zoom tertanam. Nama diisi server, tidak bisa diubah peserta. */
    public function ruang(Request $request, Rapat $rapat)
    {
        $hadir = $this->sesi($request, $rapat);
        if (! $hadir || ! $rapat->aktif) {
            return redirect()->route('masuk', $rapat);
        }

        // Mode link: arahkan ke aplikasi Zoom (cadangan: browser) dengan nama terisi
        if ($rapat->mode !== 'sdk') {
            $n = $hadir->nama_tampilan;
            $a = [$rapat->zoom_host, $rapat->meeting_number, $rapat->passcode, $n];

            return view('buka', [
                'rapat' => $rapat,
                'nama' => $n,
                'link' => [
                    'laptop' => ZoomLink::aplikasi('zoommtg', ...$a),
                    'hp' => ZoomLink::aplikasi('zoomus', ...$a),
                    'browser' => ZoomLink::browser(...$a),
                ],
            ]);
        }

        $akun = $rapat->akunZoom;
        $berlaku = max(7200, $rapat->selesai->getTimestamp() - time() + 3600);

        return view('ruang', [
            'rapat' => $rapat,
            'nama' => $hadir->nama_tampilan,
            'sdkVersi' => config('zoomhadir.sdk_versi'),
            'cfg' => [
                'signature' => ZoomSignature::buat($akun->sdk_key, $akun->sdk_secret, $rapat->meeting_number, 0, $berlaku),
                'meetingNumber' => $rapat->meeting_number,
                'passWord' => (string) $rapat->passcode,
                'userName' => $hadir->nama_tampilan,
                'pingUrl' => route('ping', $rapat),
                'leaveUrl' => route('selesai', $rapat),
                'masukUrl' => route('masuk', $rapat),
                'sdkLib' => asset('zoom-sdk/lib'),
                'detak' => config('zoomhadir.detak_detik'),
            ],
        ]);
    }

    /** Dipanggil browser: masuk | detak | keluar. */
    public function ping(Request $request, Rapat $rapat)
    {
        $hadir = $this->sesi($request, $rapat);
        if (! $hadir) {
            // Nomor yang sama login di perangkat lain, atau sesi habis
            return response()->json(['ok' => false], 409);
        }

        $kini = now();
        $hadir->durasi_detik = DurasiHadir::tambah(
            $hadir->durasi_detik,
            $hadir->masuk_at ? $hadir->terakhir_at?->getTimestamp() : null,
            $kini->getTimestamp(),
        );

        switch ($request->input('event')) {
            case 'masuk':
                $hadir->masuk_at ??= $kini;
                $hadir->terakhir_at = $kini;
                $hadir->keluar_at = null;
                break;
            case 'detak':
                if ($hadir->masuk_at) {
                    $hadir->terakhir_at = $kini;
                }
                break;
            case 'keluar':
                if ($hadir->masuk_at) {
                    $hadir->keluar_at = $kini;
                    $hadir->terakhir_at = null;   // jeda setelah keluar tidak dihitung
                }
                break;
            default:
                return response()->json(['ok' => false], 422);
        }
        $hadir->save();

        return response()->json(['ok' => true]);
    }

    /** Tujuan setelah keluar dari meeting. */
    public function selesai(Request $request, Rapat $rapat)
    {
        if ($hadir = $this->sesi($request, $rapat)) {
            if ($hadir->masuk_at && ! $hadir->keluar_at) {
                $hadir->durasi_detik = DurasiHadir::tambah($hadir->durasi_detik, $hadir->terakhir_at?->getTimestamp(), time());
                $hadir->keluar_at = now();
                $hadir->terakhir_at = null;
                $hadir->save();
            }
        }

        return view('selesai', compact('rapat'));
    }

    /** Kehadiran milik sesi browser ini, hanya bila tokennya masih yang terbaru. */
    private function sesi(Request $request, Rapat $rapat): ?Kehadiran
    {
        $s = $request->session()->get("hadir.{$rapat->id}");
        if (! $s) {
            return null;
        }
        $hadir = Kehadiran::find($s['id']);

        return ($hadir && hash_equals($hadir->token, $s['token'])) ? $hadir : null;
    }
}
