@extends('layout')
@section('judul', 'Laporan ' . $rapat->judul)
@section('admin', true)
@section('isi')
<div class="d-flex justify-content-between align-items-start mb-3">
    <div>
        <h1 class="h4 mb-1">{{ $rapat->judul }}</h1>
        <div class="text-secondary small">
            {{ $rapat->mulai->format('d/m/Y H:i') }}–{{ $rapat->selesai->format('H:i') }}
            · Hadir = masuk meeting{{ $rapat->min_persen ? ' dan mengikuti minimal ' . $rapat->min_persen . '% durasi' : '' }}
        </div>
    </div>
    <a class="btn btn-outline-primary" href="{{ route('admin.laporan', [$rapat, 'csv' => 1]) }}">Unduh CSV</a>
</div>

<div class="card shadow-sm mb-4">
    <div class="card-body">
        <form class="row g-2 align-items-center" method="post" action="{{ route('admin.laporan.zoom', $rapat) }}" enctype="multipart/form-data">
            @csrf
            <div class="col-md-5 fw-semibold">Unggah laporan peserta Zoom (CSV)</div>
            <div class="col-md-5"><input type="file" class="form-control" name="berkas" accept=".csv,text/csv" required></div>
            <div class="col-md-2"><button class="btn btn-primary w-100">Unggah</button></div>
        </form>
        <div class="small text-secondary mt-2">
            @if ($rapat->mode === 'sdk')
                Opsional: kehadiran sudah tercatat otomatis. Laporan Zoom akan menggantikan hitungan durasinya.
            @else
                Wajib untuk rapat ini: tanpa laporan Zoom, yang tercatat hanya siapa yang membuka link, bukan siapa yang hadir.
                Unduh dari portal Zoom: Reports → Usage → pilih rapat → Participants → Export.
            @endif
        </div>
    </div>
</div>

<div class="row g-3 mb-4">
    <div class="col-6 col-md-3"><div class="card shadow-sm"><div class="card-body">
        <div class="angka text-primary">{{ $ringkas['persen'] }}%</div><div class="small text-secondary">Persentase hadir</div></div></div></div>
    <div class="col-6 col-md-3"><div class="card shadow-sm"><div class="card-body">
        <div class="angka">{{ $ringkas['hadir'] }} <span class="fs-6 text-secondary">/ {{ $ringkas['terdaftar'] }}</span></div><div class="small text-secondary">Hadir dari peserta terverifikasi</div></div></div></div>
    <div class="col-6 col-md-3"><div class="card shadow-sm"><div class="card-body">
        <div class="angka">{{ $ringkas['klik_saja'] }}</div><div class="small text-secondary">Membuka link tetapi belum tercatat hadir</div></div></div></div>
    <div class="col-6 col-md-3"><div class="card shadow-sm"><div class="card-body">
        <div class="angka">{{ $ringkas['mandiri_hadir'] }}</div><div class="small text-secondary">Pendaftar mandiri hadir (belum diverifikasi, di luar persentase)</div></div></div></div>
</div>

<div class="card shadow-sm mb-4">
    <div class="card-header bg-white fw-semibold">Per bidang</div>
    <table class="table mb-0">
        <thead><tr><th>Bidang</th><th class="text-end">Terdaftar</th><th class="text-end">Hadir</th><th class="text-end">%</th></tr></thead>
        <tbody>
        @foreach ($perBidang as $nama => $b)
            <tr><td>{{ $nama }}</td><td class="text-end">{{ $b['terdaftar'] }}</td><td class="text-end">{{ $b['hadir'] }}</td><td class="text-end">{{ $b['persen'] }}%</td></tr>
        @endforeach
        </tbody>
    </table>
</div>

<div class="card shadow-sm">
    <div class="card-header bg-white fw-semibold">Rincian peserta</div>
    <div class="table-responsive">
        <table class="table table-sm mb-0">
            <thead><tr><th>Nama di Zoom</th><th>No HP</th><th>Status</th><th>Masuk</th><th>Keluar</th><th class="text-end">Durasi</th></tr></thead>
            <tbody>
            @foreach ($tampil as $b)
                <tr>
                    <td>
                        {{ $b->h->nama_tampilan ?? $b->peserta->namaTampilan() }}
                        @unless ($b->peserta->terverifikasi) <span class="badge text-bg-warning">belum diverifikasi</span> @endunless
                    </td>
                    <td>{{ $b->peserta->no_hp }}</td>
                    <td>
                        @if ($b->hadir) <span class="badge text-bg-success">Hadir</span>
                        @elseif ($b->h) <span class="badge text-bg-secondary">Tidak memenuhi</span>
                        @else <span class="badge text-bg-light border">Tidak hadir</span> @endif
                    </td>
                    <td>{{ $b->h?->masuk_at?->format('H:i:s') ?? '–' }}</td>
                    <td>{{ $b->h?->keluar_at?->format('H:i:s') ?? ($b->h?->masuk_at ? 'masih di dalam' : '–') }}</td>
                    <td class="text-end">{{ $b->h ? intdiv($b->h->durasi_detik, 60) . ' mnt' : '–' }}</td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection
