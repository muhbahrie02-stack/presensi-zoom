<!doctype html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('judul', 'Presensi Zoom')</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background: #f4f6f8; }
        .kartu-masuk { max-width: 440px; margin: 8vh auto 0; }
        .angka { font-size: 2rem; font-weight: 700; line-height: 1; }
        table td, table th { vertical-align: middle; }
    </style>
</head>
<body>
@hasSection('admin')
<nav class="navbar navbar-expand bg-white border-bottom mb-4">
    <div class="container">
        <span class="navbar-brand fw-semibold">Presensi Zoom</span>
        <div class="navbar-nav me-auto">
            <a class="nav-link @if(request()->routeIs('admin.rapat', 'admin.laporan')) active fw-semibold @endif" href="{{ route('admin.rapat') }}">Rapat</a>
            <a class="nav-link @if(request()->routeIs('admin.peserta')) active fw-semibold @endif" href="{{ route('admin.peserta') }}">Peserta</a>
            <a class="nav-link @if(request()->routeIs('admin.referensi')) active fw-semibold @endif" href="{{ route('admin.referensi') }}">Referensi</a>
        </div>
        <form method="post" action="{{ route('admin.logout') }}">@csrf
            <button class="btn btn-sm btn-outline-secondary">Keluar</button>
        </form>
    </div>
</nav>
@endif
<main class="container pb-5">
    @if (session('ok'))
        <div class="alert alert-success">{{ session('ok') }}</div>
    @endif
    @if ($errors->any())
        <div class="alert alert-danger mb-3">
            @foreach ($errors->all() as $e) <div>{{ $e }}</div> @endforeach
        </div>
    @endif
    @if (session('galat'))
        <div class="alert alert-warning small">
            <strong>Baris yang dilewati:</strong>
            @foreach (session('galat') as $g) <div>{{ $g }}</div> @endforeach
        </div>
    @endif
    @yield('isi')
</main>
</body>
</html>
