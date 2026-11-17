@extends('layout')
@section('judul', $rapat->judul)
@section('isi')
<div class="card shadow-sm kartu-masuk">
    <div class="card-body p-4">
        <h1 class="h5 mb-1">{{ $rapat->judul }}</h1>
        <p class="text-secondary small mb-4">{{ $rapat->mulai->translatedFormat('l, j F Y H:i') }} – {{ $rapat->selesai->format('H:i') }}</p>

        @if (! $rapat->aktif)
            <div class="alert alert-secondary mb-0">Rapat ini belum dibuka atau sudah ditutup.</div>
        @else
            <form method="post" action="{{ route('masuk.cek', $rapat) }}">
                @csrf
                <label for="no_hp" class="form-label">Nomor HP</label>
                <input type="tel" inputmode="numeric" autocomplete="tel" class="form-control form-control-lg mb-3"
                       id="no_hp" name="no_hp" value="{{ old('no_hp') }}" placeholder="081234567890" required autofocus>
                <button class="btn btn-primary btn-lg w-100">Masuk rapat</button>
            </form>
            <p class="text-secondary small mt-3 mb-0">Nama Anda di Zoom diisi otomatis sesuai data yang terdaftar.</p>
        @endif
    </div>
</div>
@endsection
