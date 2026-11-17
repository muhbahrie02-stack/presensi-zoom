@extends('layout')
@section('judul', 'Peserta')
@section('admin', true)
@section('isi')
<div class="row g-4">
    <div class="col-lg-8">
        <div class="card shadow-sm">
            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                <span class="fw-semibold">Peserta
                    @if ($belum) <span class="badge text-bg-warning">{{ $belum }} belum diverifikasi</span> @endif
                </span>
                <form class="d-flex gap-2" method="get">
                    <input class="form-control form-control-sm" name="cari" value="{{ request('cari') }}" placeholder="Cari nama / nomor">
                    <button class="btn btn-sm btn-outline-secondary">Cari</button>
                </form>
            </div>
            <div class="list-group list-group-flush">
                @forelse ($pesertas as $p)
                    <form class="list-group-item" method="post" action="{{ route('admin.peserta.ubah', $p) }}">
                        @csrf @method('put')
                        <div class="small text-secondary mb-1">{{ $p->namaTampilan() }}{{ $p->sumber === 'mandiri' ? ' · daftar mandiri' : '' }}</div>
                        <div class="row g-2 align-items-center">
                            <div class="col-md-2"><input class="form-control form-control-sm" name="no_hp" value="{{ $p->no_hp }}" required></div>
                            <div class="col-md-3"><input class="form-control form-control-sm" name="nama" value="{{ $p->nama }}" required></div>
                            <div class="col-md-2">
                                <select class="form-select form-select-sm" name="jabatan_id">
                                    @foreach ($jabatans as $j) <option value="{{ $j->id }}" @selected($p->jabatan_id == $j->id)>{{ $j->nama }}</option> @endforeach
                                </select>
                            </div>
                            <div class="col-md-2">
                                <select class="form-select form-select-sm" name="bidang_id">
                                    @foreach ($bidangs as $b) <option value="{{ $b->id }}" @selected($p->bidang_id == $b->id)>{{ $b->kode }}. {{ $b->nama }}</option> @endforeach
                                </select>
                            </div>
                            <div class="col-md-1">
                                <div class="form-check" title="Terverifikasi">
                                    <input class="form-check-input" type="checkbox" name="terverifikasi" value="1" id="v{{ $p->id }}" @checked($p->terverifikasi)>
                                    <label class="form-check-label small" for="v{{ $p->id }}">Sah</label>
                                </div>
                            </div>
                            <div class="col-md-2 text-end text-nowrap">
                                <button class="btn btn-sm btn-primary">Simpan</button>
                                <button class="btn btn-sm btn-outline-danger" form="hapus{{ $p->id }}">Hapus</button>
                            </div>
                        </div>
                    </form>
                    <form id="hapus{{ $p->id }}" method="post" action="{{ route('admin.peserta.hapus', $p) }}"
                          onsubmit="return confirm('Hapus peserta ini beserta riwayat kehadirannya?')">@csrf @method('delete')</form>
                @empty
                    <div class="list-group-item text-secondary text-center py-4">Belum ada peserta.</div>
                @endforelse
            </div>
            <div class="card-body">{{ $pesertas->links('pagination::bootstrap-5') }}</div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card shadow-sm mb-4">
            <div class="card-header bg-white fw-semibold">Tambah peserta</div>
            <div class="card-body">
                <form method="post" action="{{ route('admin.peserta.simpan') }}">
                    @csrf
                    <input class="form-control mb-2" name="no_hp" placeholder="Nomor HP" value="{{ old('no_hp') }}" required>
                    <input class="form-control mb-2" name="nama" placeholder="Nama tanpa gelar" value="{{ old('nama') }}" required>
                    <select class="form-select mb-2" name="jabatan_id" required>
                        <option value="">Jabatan</option>
                        @foreach ($jabatans as $j) <option value="{{ $j->id }}">{{ $j->nama }}</option> @endforeach
                    </select>
                    <select class="form-select mb-3" name="bidang_id" required>
                        <option value="">Bidang kerja</option>
                        @foreach ($bidangs as $b) <option value="{{ $b->id }}">{{ $b->kode }}. {{ $b->nama }}</option> @endforeach
                    </select>
                    <button class="btn btn-primary w-100">Tambah</button>
                </form>
            </div>
        </div>

        <div class="card shadow-sm">
            <div class="card-header bg-white fw-semibold">Impor CSV</div>
            <div class="card-body">
                <form method="post" action="{{ route('admin.peserta.impor') }}" enctype="multipart/form-data">
                    @csrf
                    <input type="file" class="form-control mb-2" name="berkas" accept=".csv,text/csv" required>
                    <button class="btn btn-outline-primary w-100">Impor</button>
                </form>
                <p class="small text-secondary mt-2 mb-0">
                    Kolom berurutan: <code>no_hp, nama, jabatan, kode_bidang</code>. Baris pertama adalah judul kolom.
                    Jabatan dan kode bidang harus sudah ada di Referensi. Nomor yang sudah ada akan diperbarui.
                </p>
            </div>
        </div>
    </div>
</div>
@endsection
