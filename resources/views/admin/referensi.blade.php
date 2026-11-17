@extends('layout')
@section('judul', 'Referensi')
@section('admin', true)
@section('isi')
<div class="row g-4">
    <div class="col-md-6">
        <div class="card shadow-sm">
            <div class="card-header bg-white fw-semibold">Bidang kerja</div>
            <ul class="list-group list-group-flush">
                @foreach ($bidangs as $b)
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        <span><code>{{ $b->kode }}</code> {{ $b->nama }}</span>
                        <form method="post" action="{{ route('admin.bidang.hapus', $b) }}">@csrf @method('delete')
                            <button class="btn btn-sm btn-outline-danger">Hapus</button></form>
                    </li>
                @endforeach
            </ul>
            <div class="card-body">
                <form class="row g-2" method="post" action="{{ route('admin.bidang.simpan') }}">
                    @csrf
                    <div class="col-3"><input class="form-control" name="kode" placeholder="Kode" required></div>
                    <div class="col-6"><input class="form-control" name="nama" placeholder="Nama bidang" required></div>
                    <div class="col-3"><button class="btn btn-primary w-100">Tambah</button></div>
                </form>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card shadow-sm">
            <div class="card-header bg-white fw-semibold">Jabatan</div>
            <ul class="list-group list-group-flush">
                @foreach ($jabatans as $j)
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        <span>{{ $j->nama }} <span class="small text-secondary">urutan {{ $j->urutan }}</span></span>
                        <form method="post" action="{{ route('admin.jabatan.hapus', $j) }}">@csrf @method('delete')
                            <button class="btn btn-sm btn-outline-danger">Hapus</button></form>
                    </li>
                @endforeach
            </ul>
            <div class="card-body">
                <form class="row g-2" method="post" action="{{ route('admin.jabatan.simpan') }}">
                    @csrf
                    <div class="col-6"><input class="form-control" name="nama" placeholder="Nama jabatan" required></div>
                    <div class="col-3"><input type="number" min="0" class="form-control" name="urutan" placeholder="Urutan"></div>
                    <div class="col-3"><button class="btn btn-primary w-100">Tambah</button></div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
