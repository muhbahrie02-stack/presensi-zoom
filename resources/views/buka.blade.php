@extends('layout')
@section('judul', $rapat->judul)
@section('isi')
<div class="card shadow-sm kartu-masuk">
    <div class="card-body p-4 text-center">
        <h1 class="h5 mb-1">{{ $rapat->judul }}</h1>
        <p class="text-secondary small mb-3">Anda masuk sebagai</p>
        <p class="fw-semibold mb-4">{{ $nama }}</p>

        <a class="btn btn-primary btn-lg w-100 mb-2" id="aplikasi" href="{{ $link['laptop'] }}">Buka di aplikasi Zoom</a>
        <a class="btn btn-outline-primary w-100" href="{{ $link['browser'] }}">Buka lewat browser</a>

        <p class="text-secondary small mt-4 mb-0">
            Jangan mengubah nama di Zoom. Bila aplikasi Zoom tidak terbuka, pakai tombol browser.
        </p>
    </div>
</div>
<script>
(function () {
    var hp = /Android|iPhone|iPad|iPod/i.test(navigator.userAgent);
    var a = document.getElementById('aplikasi');
    if (hp) a.href = @json($link['hp']);
    // Coba buka aplikasi Zoom otomatis; tombol tetap tersedia bila diblokir browser
    setTimeout(function () { window.location.href = a.href; }, 400);
})();
</script>
@endsection
