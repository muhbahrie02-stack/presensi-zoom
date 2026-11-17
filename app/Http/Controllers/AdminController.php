<?php

namespace App\Http\Controllers;

use App\Models\AkunZoom;
use App\Models\Bidang;
use App\Models\Jabatan;
use App\Models\Peserta;
use App\Models\Rapat;
use App\Support\NamaBersih;
use App\Support\LaporanZoom;
use App\Support\NomorHp;
use App\Support\ZoomLink;
use App\Models\Kehadiran;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class AdminController extends Controller
{
    // ---------- Login ----------

    public function loginForm()
    {
        return view('admin.login');
    }

    public function login(Request $request)
    {
        $sandi = (string) config('zoomhadir.admin_password');
        if ($sandi === '' || ! hash_equals($sandi, (string) $request->input('password'))) {
            return back()->withErrors(['password' => 'Kata sandi salah.']);
        }
        $request->session()->regenerate();
        $request->session()->put('admin', true);

        return redirect()->route('admin.rapat');
    }

    public function logout(Request $request)
    {
        $request->session()->forget('admin');

        return redirect()->route('admin.login');
    }

    // ---------- Rapat & akun Zoom ----------

    public function rapat()
    {
        return view('admin.rapat', [
            'rapats' => Rapat::with('akunZoom')->withCount('kehadirans')->latest('mulai')->get(),
            'akuns' => AkunZoom::orderBy('nama')->get(),
        ]);
    }

    public function rapatSimpan(Request $request)
    {
        // Link undangan Zoom boleh ditempel utuh: ID, passcode, dan alamat diambil otomatis
        if ($request->filled('link_undangan')) {
            $u = ZoomLink::urai($request->input('link_undangan'));
            if (! $u) {
                throw ValidationException::withMessages(['link_undangan' => 'Link undangan Zoom tidak dikenali.']);
            }
            $request->merge(['zoom_host' => $u['host'], 'meeting_number' => $u['meeting_number'], 'passcode' => $u['passcode']]);
        }

        $data = $request->validate([
            'judul' => ['required', 'string', 'max:150'],
            'mode' => ['required', 'in:link,sdk'],
            'akun_zoom_id' => ['nullable', 'required_if:mode,sdk', 'exists:akun_zooms,id'],
            'zoom_host' => ['nullable', 'string', 'max:60', 'regex:/^[a-z0-9.-]*zoom\.us$/i'],
            'meeting_number' => ['required', 'string', 'max:20'],
            'passcode' => ['nullable', 'string', 'max:100'],
            'mulai' => ['required', 'date'],
            'selesai' => ['required', 'date', 'after:mulai'],
            'min_persen' => ['required', 'integer', 'between:0,100'],
        ], ['meeting_number.required' => 'Tempel link undangan Zoom atau isi Meeting ID.']);

        $data['meeting_number'] = preg_replace('/\D+/', '', $data['meeting_number']);
        $data['zoom_host'] = strtolower($data['zoom_host'] ?? '') ?: 'zoom.us';
        $data['slug'] = Str::lower(Str::random(8));
        if ($data['mode'] === 'link') {
            $data['akun_zoom_id'] = null;
        }

        Rapat::create($data);

        return back()->with('ok', 'Rapat dibuat.');
    }

    /**
     * Mengisi kehadiran dari laporan peserta Zoom (CSV). Dicocokkan lewat nama
     * tampilan; peserta yang mengganti nama dikenali dari nama aslinya.
     */
    public function laporanZoom(Request $request, Rapat $rapat)
    {
        $request->validate(['berkas' => ['required', 'file', 'max:4096']]);

        $laporan = LaporanZoom::baca(file_get_contents($request->file('berkas')->getRealPath()));
        if (! $laporan) {
            return back()->withErrors(['berkas' => 'Kolom nama dan durasi tidak ditemukan. Unduh laporan dari Reports > Usage > Participants.']);
        }

        // nama tampilan (ternormalisasi) -> peserta
        $peta = Peserta::with(['bidang', 'jabatan'])->get()
            ->mapWithKeys(fn (Peserta $p) => [LaporanZoom::kunci($p->namaTampilan()) => $p]);

        // Beberapa baris laporan bisa menunjuk orang yang sama (masuk ulang dengan nama diganti)
        $perPeserta = [];
        $asing = [];
        foreach ($laporan as $kunci => $l) {
            $p = $peta[$kunci] ?? ($l['asli'] ? ($peta[$l['asli']] ?? null) : null);
            if (! $p) {
                $asing[] = $l['nama'];
                continue;
            }
            $x = $perPeserta[$p->id] ?? ['peserta' => $p, 'durasi' => 0, 'masuk' => null, 'keluar' => null];
            $x['durasi'] += $l['durasi_detik'];
            $waktu = array_filter([$x['masuk'], $l['masuk']]);
            $x['masuk'] = $waktu ? min($waktu) : null;
            $x['keluar'] = max($x['keluar'] ?? 0, $l['keluar'] ?? 0) ?: null;
            $perPeserta[$p->id] = $x;
        }

        foreach ($perPeserta as $x) {
            $h = Kehadiran::firstOrNew(['rapat_id' => $rapat->id, 'peserta_id' => $x['peserta']->id]);
            $h->nama_tampilan ??= $x['peserta']->namaTampilan();
            $h->token ??= Str::random(48);
            $masuk = $x['masuk'] ? \Illuminate\Support\Carbon::createFromTimestamp($x['masuk']) : $rapat->mulai;
            $h->klik_at ??= $masuk;
            $h->masuk_at = $masuk;
            $h->keluar_at = $x['keluar'] ? \Illuminate\Support\Carbon::createFromTimestamp($x['keluar']) : null;
            $h->terakhir_at = null;
            $h->durasi_detik = $x['durasi'];   // laporan Zoom menggantikan hitungan sebelumnya
            $h->save();
        }

        return back()
            ->with('ok', count($perPeserta) . ' peserta dicocokkan dari laporan Zoom.')
            ->with('galat', array_map(fn ($n) => "Tidak dikenali: $n", array_slice($asing, 0, 40)));
    }

    public function rapatAktif(Rapat $rapat)
    {
        $rapat->update(['aktif' => ! $rapat->aktif]);

        return back();
    }

    public function rapatHapus(Rapat $rapat)
    {
        $rapat->delete();

        return back()->with('ok', 'Rapat dihapus.');
    }

    public function akunSimpan(Request $request)
    {
        AkunZoom::create($request->validate([
            'nama' => ['required', 'string', 'max:100'],
            'sdk_key' => ['required', 'string', 'max:100'],
            'sdk_secret' => ['required', 'string', 'max:200'],
        ]));

        return back()->with('ok', 'Akun Zoom ditambahkan.');
    }

    public function akunHapus(AkunZoom $akun)
    {
        if (Rapat::where('akun_zoom_id', $akun->id)->exists()) {
            return back()->withErrors(['akun' => 'Akun masih dipakai oleh rapat.']);
        }
        $akun->delete();

        return back()->with('ok', 'Akun Zoom dihapus.');
    }

    // ---------- Laporan ----------

    public function laporan(Request $request, Rapat $rapat)
    {
        $pesertas = Peserta::with(['bidang', 'jabatan'])->orderBy('bidang_id')->orderBy('nama')->get();
        $hadirs = $rapat->kehadirans()->get()->keyBy('peserta_id');

        $baris = $pesertas->map(function (Peserta $p) use ($hadirs, $rapat) {
            $h = $hadirs->get($p->id);

            return (object) [
                'peserta' => $p,
                'h' => $h,
                'hadir' => $h ? $h->hadir($rapat) : false,
            ];
        });

        // Penyebut persentase = peserta yang sudah diverifikasi admin
        $resmi = $baris->filter(fn ($b) => $b->peserta->terverifikasi);
        $mandiri = $baris->filter(fn ($b) => ! $b->peserta->terverifikasi && $b->h);

        $ringkas = [
            'terdaftar' => $resmi->count(),
            'hadir' => $resmi->where('hadir', true)->count(),
            'klik_saja' => $resmi->filter(fn ($b) => $b->h && ! $b->hadir)->count(),
            'mandiri_hadir' => $mandiri->where('hadir', true)->count(),
        ];
        $ringkas['persen'] = $ringkas['terdaftar'] ? round($ringkas['hadir'] / $ringkas['terdaftar'] * 100, 1) : 0;

        $perBidang = $resmi->groupBy(fn ($b) => $b->peserta->bidang->kode . '.' . $b->peserta->bidang->nama)
            ->map(fn ($g) => [
                'terdaftar' => $g->count(),
                'hadir' => $g->where('hadir', true)->count(),
                'persen' => round($g->where('hadir', true)->count() / $g->count() * 100, 1),
            ])->sortKeys();

        if ($request->boolean('csv')) {
            return $this->csv($rapat, $baris);
        }

        // Di layar: semua peserta resmi + pendaftar mandiri yang ikut rapat ini
        $tampil = $baris->filter(fn ($b) => $b->peserta->terverifikasi || $b->h)->values();

        return view('admin.laporan', compact('rapat', 'ringkas', 'perBidang', 'tampil'));
    }

    private function csv(Rapat $rapat, $baris)
    {
        $nama = 'kehadiran-' . Str::slug($rapat->judul) . '.csv';

        return response()->streamDownload(function () use ($baris) {
            $f = fopen('php://output', 'w');
            fwrite($f, "\xEF\xBB\xBF");   // BOM agar Excel membaca UTF-8
            fputcsv($f, ['No HP', 'Nama', 'Jabatan', 'Kode', 'Bidang', 'Terverifikasi', 'Status', 'Klik', 'Masuk', 'Keluar', 'Durasi (menit)']);
            foreach ($baris as $b) {
                if (! $b->peserta->terverifikasi && ! $b->h) {
                    continue;
                }
                fputcsv($f, [
                    "'" . $b->peserta->no_hp,
                    $b->peserta->nama,
                    $b->peserta->jabatan->nama,
                    $b->peserta->bidang->kode,
                    $b->peserta->bidang->nama,
                    $b->peserta->terverifikasi ? 'Ya' : 'Belum',
                    $b->hadir ? 'Hadir' : ($b->h ? 'Tidak memenuhi' : 'Tidak hadir'),
                    $b->h?->klik_at?->format('Y-m-d H:i:s'),
                    $b->h?->masuk_at?->format('Y-m-d H:i:s'),
                    $b->h?->keluar_at?->format('Y-m-d H:i:s'),
                    $b->h ? round($b->h->durasi_detik / 60, 1) : '',
                ]);
            }
            fclose($f);
        }, $nama, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    // ---------- Peserta ----------

    public function peserta(Request $request)
    {
        $q = Peserta::with(['bidang', 'jabatan'])->orderBy('terverifikasi')->orderBy('nama');
        if ($request->filled('cari')) {
            $cari = $request->input('cari');
            $q->where(fn ($w) => $w->where('nama', 'like', "%$cari%")->orWhere('no_hp', 'like', "%$cari%"));
        }

        return view('admin.peserta', [
            'pesertas' => $q->paginate(50)->withQueryString(),
            'bidangs' => Bidang::orderBy('kode')->get(),
            'jabatans' => Jabatan::orderBy('urutan')->orderBy('nama')->get(),
            'belum' => Peserta::where('terverifikasi', false)->count(),
        ]);
    }

    public function pesertaSimpan(Request $request)
    {
        $data = $this->validasiPeserta($request);
        if (Peserta::where('no_hp', $data['no_hp'])->exists()) {
            return back()->withErrors(['no_hp' => 'Nomor HP sudah terdaftar.'])->withInput();
        }
        Peserta::create($data + ['terverifikasi' => true, 'sumber' => 'admin']);

        return back()->with('ok', 'Peserta ditambahkan.');
    }

    public function pesertaUbah(Request $request, Peserta $peserta)
    {
        $data = $this->validasiPeserta($request);
        if (Peserta::where('no_hp', $data['no_hp'])->where('id', '!=', $peserta->id)->exists()) {
            return back()->withErrors(['no_hp' => 'Nomor HP dipakai peserta lain.']);
        }
        $peserta->update($data + ['terverifikasi' => $request->boolean('terverifikasi')]);

        return back()->with('ok', 'Peserta diperbarui.');
    }

    public function pesertaHapus(Peserta $peserta)
    {
        $peserta->delete();

        return back()->with('ok', 'Peserta dihapus.');
    }

    private function validasiPeserta(Request $request): array
    {
        $data = $request->validate([
            'no_hp' => ['required', 'string'],
            'nama' => ['required', 'string', 'max:60'],
            'bidang_id' => ['required', 'exists:bidangs,id'],
            'jabatan_id' => ['required', 'exists:jabatans,id'],
        ]);
        $data['no_hp'] = NomorHp::normal($data['no_hp']);
        if (! $data['no_hp']) {
            throw ValidationException::withMessages(['no_hp' => 'Nomor HP tidak valid.']);
        }
        // Nama dari admin dipakai apa adanya: admin yang memutuskan penulisan akhirnya
        $data['nama'] = trim($data['nama']);

        return $data;
    }

    /** Impor CSV: no_hp,nama,jabatan,kode_bidang (baris pertama = judul kolom). */
    public function pesertaImpor(Request $request)
    {
        $request->validate(['berkas' => ['required', 'file', 'max:2048']]);

        $bidang = Bidang::pluck('id', 'kode');
        $jabatan = Jabatan::all()->mapWithKeys(fn ($j) => [mb_strtolower($j->nama) => $j->id]);

        $f = fopen($request->file('berkas')->getRealPath(), 'r');
        $kepala = fgets($f);
        $pemisah = substr_count($kepala, ';') > substr_count($kepala, ',') ? ';' : ',';

        $masuk = 0;
        $galat = [];
        $no = 1;
        while (($r = fgetcsv($f, 0, $pemisah)) !== false) {
            $no++;
            if (count(array_filter($r, fn ($x) => trim((string) $x) !== '')) === 0) {
                continue;
            }
            [$hp, $nama, $jab, $kode] = array_map('trim', array_pad($r, 4, ''));
            $hp = NomorHp::normal($hp);
            $bid = $bidang[$kode] ?? null;
            $jid = $jabatan[mb_strtolower($jab)] ?? null;

            if (! $hp || $nama === '' || ! $bid || ! $jid) {
                $galat[] = "Baris $no: " . (! $hp ? 'nomor HP tidak valid' : ($nama === '' ? 'nama kosong' : (! $bid ? "kode bidang \"$kode\" tidak dikenal" : "jabatan \"$jab\" tidak dikenal")));
                continue;
            }
            Peserta::updateOrCreate(['no_hp' => $hp], [
                'nama' => NamaBersih::bersihkan($nama),
                'bidang_id' => $bid, 'jabatan_id' => $jid,
                'terverifikasi' => true, 'sumber' => 'admin',
            ]);
            $masuk++;
        }
        fclose($f);

        return back()->with('ok', "$masuk peserta diimpor.")->with('galat', array_slice($galat, 0, 30));
    }

    // ---------- Referensi ----------

    public function referensi()
    {
        return view('admin.referensi', [
            'bidangs' => Bidang::orderBy('kode')->get(),
            'jabatans' => Jabatan::orderBy('urutan')->orderBy('nama')->get(),
        ]);
    }

    public function bidangSimpan(Request $request)
    {
        Bidang::create($request->validate([
            'kode' => ['required', 'string', 'max:10', 'unique:bidangs,kode'],
            'nama' => ['required', 'string', 'max:80'],
        ]));

        return back()->with('ok', 'Bidang ditambahkan.');
    }

    public function bidangHapus(Bidang $bidang)
    {
        if (Peserta::where('bidang_id', $bidang->id)->exists()) {
            return back()->withErrors(['bidang' => 'Bidang masih dipakai peserta.']);
        }
        $bidang->delete();

        return back();
    }

    public function jabatanSimpan(Request $request)
    {
        $data = $request->validate([
            'nama' => ['required', 'string', 'max:60', 'unique:jabatans,nama'],
            'urutan' => ['nullable', 'integer', 'min:0'],
        ]);
        $data['urutan'] ??= 0;
        Jabatan::create($data);

        return back()->with('ok', 'Jabatan ditambahkan.');
    }

    public function jabatanHapus(Jabatan $jabatan)
    {
        if (Peserta::where('jabatan_id', $jabatan->id)->exists()) {
            return back()->withErrors(['jabatan' => 'Jabatan masih dipakai peserta.']);
        }
        $jabatan->delete();

        return back();
    }
}
