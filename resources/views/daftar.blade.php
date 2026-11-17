@extends('layout')
@section('judul', 'Pendaftaran peserta')
@section('isi')
<div class="card shadow-sm kartu-masuk">
    <div class="card-body p-4">
        <h1 class="h5 mb-1">Nomor belum terdaftar</h1>
        <p class="text-secondary small mb-4">Lengkapi data berikut, lalu Anda langsung masuk ke rapat.</p>

        <form method="post" action="{{ route('daftar.simpan', $rapat) }}">
            @csrf
            <div class="mb-3">
                <label class="form-label">Nomor HP</label>
                <input class="form-control" value="{{ $noHp }}" disabled>
                <div class="form-text"><a href="{{ route('masuk', $rapat) }}">Ganti nomor</a></div>
            </div>
            <div class="mb-3">
                <label for="nama" class="form-label">Nama (tanpa gelar)</label>
                <input class="form-control" id="nama" name="nama" value="{{ old('nama') }}" maxlength="60" required autofocus>
            </div>
            <div class="mb-3">
                <label for="jabatan_id" class="form-label">Jabatan</label>
                <select class="form-select" id="jabatan_id" name="jabatan_id" required>
                    <option value="">Pilih jabatan</option>
                    @foreach ($jabatans as $j)
                        <option value="{{ $j->id }}" @selected(old('jabatan_id') == $j->id)>{{ $j->nama }}</option>
                    @endforeach
                </select>
            </div>
            <div class="mb-4">
                <label for="bidang_id" class="form-label">Bidang kerja</label>
                <select class="form-select" id="bidang_id" name="bidang_id" required>
                    <option value="">Pilih bidang kerja</option>
                    @foreach ($bidangs as $b)
                        <option value="{{ $b->id }}" @selected(old('bidang_id') == $b->id)>{{ $b->kode }}. {{ $b->nama }}</option>
                    @endforeach
                </select>
            </div>
            <button class="btn btn-primary btn-lg w-100">Daftar dan masuk rapat</button>
        </form>
    </div>
</div>
@endsection
