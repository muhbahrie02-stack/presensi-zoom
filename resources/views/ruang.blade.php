<!doctype html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $rapat->judul }}</title>
    {{-- Aset Meeting SDK disalin ke public/zoom-sdk (lihat PASANG.md) --}}
    <link rel="stylesheet" href="{{ asset('zoom-sdk/lib/ui/zoom-meetingsdk.css') }}">
    <style>
        #status { position: fixed; inset: 0; display: flex; align-items: center; justify-content: center;
                  font: 16px/1.5 system-ui, sans-serif; color: #333; background: #f4f6f8; text-align: center; padding: 24px; }
        #status.sembunyi { display: none; }
    </style>
</head>
<body>
<div id="status">Menyiapkan rapat sebagai<br><strong>{{ $nama }}</strong></div>

<script src="{{ asset('zoom-sdk/lib/vendor/react.min.js') }}"></script>
<script src="{{ asset('zoom-sdk/lib/vendor/react-dom.min.js') }}"></script>
<script src="{{ asset('zoom-sdk/lib/vendor/redux.min.js') }}"></script>
<script src="{{ asset('zoom-sdk/lib/vendor/react-redux.min.js') }}"></script>
<script src="{{ asset('zoom-sdk/lib/vendor/redux-thunk.min.js') }}"></script>
<script src="{{ asset('zoom-sdk/lib/vendor/lodash.min.js') }}"></script>
<script src="{{ asset('zoom-sdk/zoom-meeting-' . $sdkVersi . '.min.js') }}"></script>
<script>
(function () {
    const cfg = {!! json_encode($cfg, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) !!};
    const csrf = document.querySelector('meta[name=csrf-token]').content;
    const elStatus = document.getElementById('status');
    let tersambung = false, timer = null;

    function pesan(teks) { elStatus.classList.remove('sembunyi'); elStatus.textContent = teks; }

    // Lapor ke server: masuk | detak | keluar
    function ping(event) {
        return fetch(cfg.pingUrl, {
            method: 'POST',
            keepalive: true,
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' },
            body: JSON.stringify({ event }),
        }).then(function (r) {
            if (r.status === 409) { digantikan(); }
        }).catch(function () { /* koneksi putus sesaat: detak berikutnya mencoba lagi */ });
    }

    // Nomor yang sama masuk dari perangkat lain
    function digantikan() {
        clearInterval(timer);
        tersambung = false;
        try { ZoomMtg.leaveMeeting({}); } catch (e) {}
        alert('Nomor HP ini masuk dari perangkat lain. Sesi di perangkat ini ditutup.');
        location.href = cfg.masukUrl;
    }

    function mulai() {
        if (tersambung) return;
        tersambung = true;
        elStatus.classList.add('sembunyi');
        ping('masuk');
        timer = setInterval(function () { ping('detak'); }, cfg.detak * 1000);
    }

    function berhenti() {
        if (!tersambung) return;
        tersambung = false;
        clearInterval(timer);
        ping('keluar');
    }

    ZoomMtg.setZoomJSLib(cfg.sdkLib, '/av');
    ZoomMtg.preLoadWasm();
    ZoomMtg.prepareWebSDK();

    ZoomMtg.i18n.load('id-ID').catch(function () {}).then(function () {
        ZoomMtg.init({
            leaveUrl: cfg.leaveUrl,
            patchJsMedia: true,
            success: function () {
                // 1 menyambung, 2 tersambung, 3 terputus, 4 menyambung ulang
                ZoomMtg.inMeetingServiceListener('onMeetingStatus', function (data) {
                    if (data.meetingStatus === 2 || data.status === 2) mulai();
                    if (data.meetingStatus === 3 || data.status === 3) berhenti();
                });

                ZoomMtg.join({
                    signature: cfg.signature,
                    meetingNumber: cfg.meetingNumber,
                    userName: cfg.userName,
                    passWord: cfg.passWord,
                    success: function () { elStatus.classList.add('sembunyi'); },
                    error: function (err) {
                        console.error(err);
                        pesan('Gagal masuk rapat: ' + (err && (err.errorMessage || err.message) || 'kesalahan tidak dikenal') + '. Muat ulang halaman atau hubungi panitia.');
                    },
                });
            },
            error: function (err) {
                console.error(err);
                pesan('Zoom gagal dimuat di browser ini. Coba Chrome atau Safari versi terbaru.');
            },
        });
    });

    // Tab ditutup / pindah halaman
    window.addEventListener('pagehide', berhenti);
})();
</script>
</body>
</html>
