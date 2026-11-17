@extends('layout')
@section('judul', 'Rapat')
@section('admin', true)
@section('isi')
<div class="row g-4">
    <div class="col-lg-8">
        <div class="card shadow-sm">
            <div class="card-header bg-white fw-semibold">Daftar rapat</div>
            <div class="table-responsive">
                <table class="table mb-0">
                    <thead><tr><th>Rapat</th><th>Link peserta</th><th class="text-end">Login</th><th></th></tr></thead>
                    <tbody>
                    @forelse ($rapats as $r)
                        <tr>
                            <td>
                                <div class="fw-semibold">{{ $r->judul }}</div>
                                <div class="small text-secondary">
                                    {{ $r->mulai->format('d/m/Y H:i') }}–{{ $r->selesai->format('H:i') }}
                                    · {{ $r->mode === 'sdk' ? 'Zoom tertanam (' . $r->akunZoom?->nama . ')' : 'Link Zoom' }} · {{ $r->zoom_host }} · ID {{ $r->meeting_number }}
                                    · hadir ≥ {{ $r->min_persen }}%
                                </div>
                            </td>
                            <td><input class="form-control form-control-sm" readonly value="{{ route('masuk', $r) }}" onclick="this.select()"></td>
                            <td class="text-end">{{ $r->kehadirans_count }}</td>
                            <td class="text-end text-nowrap">
                                <a class="btn btn-sm btn-primary" href="{{ route('admin.laporan', $r) }}">Laporan</a>
                                <form class="d-inline" method="post" action="{{ route('admin.rapat.aktif', $r) }}">@csrf
                                    <button class="btn btn-sm {{ $r->aktif ? 'btn-outline-success' : 'btn-outline-secondary' }}">{{ $r->aktif ? 'Dibuka' : 'Ditutup' }}</button>
                                </form>
                                <form class="d-inline" method="post" action="{{ route('admin.rapat.hapus', $r) }}"
                                      onsubmit="return confirm('Hapus rapat ini beserta data kehadirannya?')">@csrf @method('delete')
                                    <button class="btn btn-sm btn-outline-danger">Hapus</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="text-secondary text-center py-4">Belum ada rapat.</td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card shadow-sm mb-4">
            <div class="card-header bg-white fw-semibold">Rapat baru</div>
            <div class="card-body">
                <form method="post" action="{{ route('admin.rapat.simpan') }}">
                    @csrf
                    <input class="form-control mb-2" name="judul" placeholder="Judul rapat" value="{{ old('judul') }}" required>
                    <label class="form-label small mb-0">Link undangan Zoom</label>
                    <input class="form-control mb-2" name="link_undangan" placeholder="https://telkomsel.zoom.us/j/...?pwd=..." value="{{ old('link_undangan') }}">
                    <label class="form-label small mb-0">Cara masuk</label>
                    <select class="form-select mb-2" name="mode" id="mode" required>
                        <option value="link" @selected(old('mode', 'link') === 'link')>Link Zoom (tanpa app Marketplace)</option>
                        <option value="sdk" @selected(old('mode') === 'sdk') @disabled($akuns->isEmpty())>Zoom tertanam (butuh akun Meeting SDK)</option>
                    </select>
                    <select class="form-select mb-2" name="akun_zoom_id" id="akun">
                        <option value="">Akun Zoom host (hanya untuk Zoom tertanam)</option>
                        @foreach ($akuns as $a)
                            <option value="{{ $a->id }}" @selected(old('akun_zoom_id') == $a->id)>{{ $a->nama }}</option>
                        @endforeach
                    </select>
                    <details class="mb-2 small">
                        <summary>Isi manual bila tidak ada link undangan</summary>
                        <input class="form-control form-control-sm my-1" name="meeting_number" placeholder="Meeting ID" value="{{ old('meeting_number') }}">
                        <input class="form-control form-control-sm mb-1" name="passcode" placeholder="Passcode" value="{{ old('passcode') }}">
                        <input class="form-control form-control-sm" name="zoom_host" placeholder="Alamat Zoom, mis. telkomsel.zoom.us" value="{{ old('zoom_host') }}">
                    </details>
                    <label class="form-label small mb-0">Mulai</label>
                    <input type="datetime-local" class="form-control mb-2" name="mulai" value="{{ old('mulai') }}" required>
                    <label class="form-label small mb-0">Selesai</label>
                    <input type="datetime-local" class="form-control mb-2" name="selesai" value="{{ old('selesai') }}" required>
                    <label class="form-label small mb-0">Syarat hadir: minimal % durasi rapat (0 = cukup masuk)</label>
                    <input type="number" min="0" max="100" class="form-control mb-3" name="min_persen" value="{{ old('min_persen', 0) }}" required>
                    <button class="btn btn-primary w-100">Buat rapat</button>
                </form>
            </div>
        </div>

        <div class="card shadow-sm">
            <div class="card-header bg-white fw-semibold">Akun Meeting SDK (opsional)</div>
            <ul class="list-group list-group-flush">
                @foreach ($akuns as $a)
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        <span>{{ $a->nama }} <span class="small text-secondary">{{ Str::limit($a->sdk_key, 8) }}</span></span>
                        <form method="post" action="{{ route('admin.akun.hapus', $a) }}">@csrf @method('delete')
                            <button class="btn btn-sm btn-outline-danger">Hapus</button>
                        </form>
                    </li>
                @endforeach
            </ul>
            <div class="card-body">
                <form method="post" action="{{ route('admin.akun.simpan') }}" autocomplete="off">
                    @csrf
                    <input class="form-control mb-2" name="nama" placeholder="Nama akun, mis. dppwi.adum" required>
                    <input class="form-control mb-2" name="sdk_key" placeholder="Client ID" required>
                    <input type="password" class="form-control mb-2" name="sdk_secret" placeholder="Client Secret" required>
                    <button class="btn btn-outline-primary w-100">Tambah akun</button>
                </form>
                <p class="small text-secondary mt-2 mb-0">Hanya diperlukan untuk cara masuk “Zoom tertanam”. Host rapat harus akun Zoom yang membuat app Meeting SDK tersebut.</p>
            </div>
        </div>
    </div>
</div>
@endsection
