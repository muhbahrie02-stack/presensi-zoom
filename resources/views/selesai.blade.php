@extends('layout')
@section('judul', 'Anda keluar dari rapat')
@section('isi')
<div class="card shadow-sm kartu-masuk">
    <div class="card-body p-4 text-center">
        <h1 class="h5">Anda sudah keluar dari rapat</h1>
        <p class="text-secondary">Kehadiran Anda di “{{ $rapat->judul }}” sudah tercatat.</p>
        <a class="btn btn-outline-primary" href="{{ route('masuk', $rapat) }}">Masuk lagi</a>
    </div>
</div>
@endsection
