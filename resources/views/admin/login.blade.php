@extends('layout')
@section('judul', 'Masuk admin')
@section('isi')
<div class="card shadow-sm kartu-masuk">
    <div class="card-body p-4">
        <h1 class="h5 mb-3">Admin Presensi Zoom</h1>
        <form method="post" action="{{ route('admin.login') }}">
            @csrf
            <label for="password" class="form-label">Kata sandi</label>
            <input type="password" class="form-control mb-3" id="password" name="password" required autofocus>
            <button class="btn btn-primary w-100">Masuk</button>
        </form>
    </div>
</div>
@endsection
