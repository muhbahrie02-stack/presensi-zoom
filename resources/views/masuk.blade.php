{{-- Halaman Sambutan + Masuk (desain Wahdah Islamiyah). Berdiri sendiri, tidak extends layout. --}}
<!doctype html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>{{ $rapat->judul }} — Wahdah Islamiyah</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700;800&family=Amiri:wght@700&display=swap" rel="stylesheet">
<style>
:root{--biru:#15325f;--teal:#1f8f93;--teal-tua:#14666a;--emas:#eaa21b;--emas-tua:#c9871a;--hijau:#2e8b45;--merah:#c0152b}
*{box-sizing:border-box;margin:0;padding:0}
body{font-family:'Montserrat',system-ui,sans-serif;color:var(--biru);min-height:100vh;display:flex;align-items:center;justify-content:center;padding:20px;
  background:radial-gradient(1100px 600px at 50% -10%,rgba(31,143,147,.18),transparent 60%),radial-gradient(900px 500px at 50% 120%,rgba(234,162,27,.12),transparent 60%),linear-gradient(160deg,#eaf5f4,#f6fbfb)}
.frame{width:420px;max-width:100%;min-height:620px;position:relative;border-radius:28px;overflow:hidden;background:#fff;box-shadow:0 28px 66px -20px rgba(21,50,95,.42)}
.screen{position:absolute;inset:0;display:flex;flex-direction:column;transition:opacity .5s cubic-bezier(.4,0,.2,1),transform .5s cubic-bezier(.4,0,.2,1)}
.screen.keluar-kiri{opacity:0;transform:translateX(-46px);pointer-events:none}
.screen.masuk-kanan{opacity:0;transform:translateX(46px);pointer-events:none}
button{font-family:inherit;cursor:pointer;border:none;transition:transform .12s ease,filter .2s}
button:active{transform:scale(.96)}
.sambut{flex:1;display:flex;flex-direction:column;align-items:center;justify-content:center;text-align:center;color:#fff;padding:46px 30px;position:relative;overflow:hidden;
  background:radial-gradient(520px 320px at 50% 8%,rgba(255,255,255,.18),transparent 60%),linear-gradient(165deg,#1f9397,#14666a 55%,#0f4b53)}
.sambut::after{content:"";position:absolute;inset:0;opacity:.14;background-image:repeating-linear-gradient(90deg,#fff 0 1px,transparent 1px 44px),repeating-linear-gradient(0deg,#fff 0 1px,transparent 1px 44px);mask:radial-gradient(circle at 50% 40%,#000,transparent 70%)}
.sambut>*{position:relative;z-index:1}
.lingkar{width:180px;height:180px;border-radius:50%;background:#fff;display:flex;align-items:center;justify-content:center;margin-bottom:22px;box-shadow:0 16px 36px -10px rgba(0,0,0,.4);animation:pop .7s cubic-bezier(.34,1.56,.64,1) both}
.lingkar img{width:146px}
.marhaban{font-family:'Amiri',serif;font-size:38px;color:#ffe9b8;direction:rtl;margin-bottom:12px;animation:up .6s .1s both}
.selamat{font-size:12px;letter-spacing:.3em;text-transform:uppercase;opacity:.85;margin-bottom:24px;animation:up .6s .18s both}
.sambut h1{font-size:22px;font-weight:800;line-height:1.3;margin-bottom:8px;animation:up .6s .26s both}
.sambut .org{font-size:12px;letter-spacing:.14em;opacity:.9;text-transform:uppercase;margin-bottom:4px;animation:up .6s .32s both}
.sambut .tgl{font-size:12.5px;opacity:.8;margin-bottom:32px;animation:up .6s .38s both}
.mulai{background:linear-gradient(135deg,var(--emas),var(--emas-tua));color:#3a2600;font-weight:800;font-size:16px;border-radius:14px;padding:16px 40px;letter-spacing:.03em;box-shadow:0 14px 28px -8px rgba(0,0,0,.4);display:inline-flex;gap:10px;align-items:center;animation:up .6s .46s both}
.mulai:hover{filter:brightness(1.05)}
.mulai svg{transition:transform .2s}.mulai:hover svg{transform:translateX(3px)}
.atas{position:relative;text-align:center;padding:28px 26px 18px;background:linear-gradient(160deg,#fff,#f2f9f9);border-bottom:1px solid #eef3f3}
.atas::before{content:"";position:absolute;left:0;right:0;top:0;height:5px;background:linear-gradient(90deg,var(--teal),var(--hijau),var(--emas),var(--merah))}
.logo{width:118px;margin:4px auto 10px;display:block;filter:drop-shadow(0 8px 14px rgba(21,50,95,.18))}
.nama{font-weight:800;letter-spacing:.14em;font-size:14px}
.sub{font-size:11px;letter-spacing:.22em;color:var(--teal-tua);font-weight:600;margin-top:4px;text-transform:uppercase}
.isi{padding:22px 28px 24px;flex:1;display:flex;flex-direction:column}
.judul{text-align:center;font-weight:700;font-size:18px;margin-bottom:4px;color:var(--teal)}
.tgl2{text-align:center;font-size:12.5px;color:#6a7b86;margin-bottom:20px}
label{display:block;font-size:12.5px;font-weight:600;margin-bottom:7px;color:var(--teal-tua)}
.inp{position:relative;margin-bottom:14px}
.inp input{width:100%;padding:15px 16px 15px 46px;font-size:16px;font-family:inherit;border:1.6px solid #dce6e6;border-radius:14px;color:var(--biru);background:#fbfdfd;transition:.15s}
.inp input:focus{outline:none;border-color:var(--teal);background:#fff;box-shadow:0 0 0 4px rgba(31,143,147,.12)}
.inp .ikon{position:absolute;left:15px;top:50%;transform:translateY(-50%);color:var(--teal)}
.galat{background:#fdecec;color:#b02133;border:1px solid #f5c2c7;border-radius:12px;padding:11px 14px;font-size:12.5px;margin-bottom:14px}
.tombol{width:100%;color:#fff;font-weight:700;font-size:16px;padding:16px;border-radius:14px;background:linear-gradient(135deg,var(--teal),var(--teal-tua));box-shadow:0 10px 22px -8px rgba(20,102,106,.65);display:inline-flex;gap:9px;align-items:center;justify-content:center;position:relative;overflow:hidden}
.tombol:hover{filter:brightness(1.06)}
.tombol[disabled]{filter:saturate(.85);cursor:default}
.spin{width:18px;height:18px;border:2.5px solid rgba(255,255,255,.4);border-top-color:#fff;border-radius:50%;animation:spin .7s linear infinite}
.ket{display:flex;gap:9px;align-items:flex-start;margin-top:16px;padding:12px 14px;background:#f3f9f9;border-radius:12px;font-size:12px;color:#4a6670;line-height:1.5}
.ket svg{flex:none;margin-top:1px;color:var(--teal)}
.tutup{flex:1;display:flex;flex-direction:column;align-items:center;justify-content:center;text-align:center;padding:50px 34px;color:var(--teal-tua)}
.kaki{text-align:center;font-size:11px;color:#9aa8b0;padding:14px;border-top:1px solid #eef3f3;background:#fbfdfd}
.kaki b{color:var(--teal-tua)}
.ripple{position:absolute;border-radius:50%;background:rgba(255,255,255,.5);transform:scale(0);animation:rip .6s ease-out;pointer-events:none}
@keyframes pop{from{opacity:0;transform:scale(.6)}to{opacity:1;transform:scale(1)}}
@keyframes up{from{opacity:0;transform:translateY(16px)}to{opacity:1;transform:translateY(0)}}
@keyframes spin{to{transform:rotate(360deg)}}
@keyframes rip{to{transform:scale(4);opacity:0}}
</style>
</head>
<body>
@php($adaGalat = $errors->any())
<div class="frame">

  {{-- SAMBUTAN --}}
  <div class="screen {{ $adaGalat ? 'keluar-kiri' : '' }}" id="s1">
    <div class="sambut">
      <div class="lingkar"><img src="{{ asset('logo.png') }}" alt="Logo Wahdah Islamiyah"></div>
      <div class="marhaban">&#1605;&#1585;&#1581;&#1576;&#1611;&#1575; &#1576;&#1603;&#1605;</div>
      <div class="selamat">Selamat Datang</div>
      <h1>{{ $rapat->judul }}</h1>
      <div class="org">Dewan Pengurus Pusat Wahdah Islamiyah</div>
      <div class="tgl">{{ $rapat->mulai->translatedFormat('l, j F Y') }}</div>
      @if ($rapat->aktif)
        <button class="mulai" onclick="keForm(this,event)">Silahkan Masuk
          <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.6" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14M13 6l6 6-6 6"/></svg>
        </button>
      @else
        <div style="opacity:.85;font-size:13px">Rapat ini belum dibuka atau sudah ditutup.</div>
      @endif
    </div>
  </div>

  {{-- MASUK --}}
  <div class="screen {{ $adaGalat ? '' : 'masuk-kanan' }}" id="s2">
    <div class="atas">
      <img class="logo" src="{{ asset('logo.png') }}" alt="Logo Wahdah Islamiyah">
      <div class="nama">WAHDAH ISLAMIYAH</div>
      <div class="sub">Dewan Pengurus Pusat</div>
    </div>
    @if ($rapat->aktif)
      <div class="isi">
        <div class="judul">{{ $rapat->judul }}</div>
        <div class="tgl2">{{ $rapat->mulai->translatedFormat('l, j F Y') }} &middot; {{ $rapat->mulai->format('H.i') }} WITA</div>

        <form method="post" action="{{ route('masuk.cek', $rapat) }}" onsubmit="kirim(this)">
          @csrf
          <label for="no_hp">Nomor HP terdaftar</label>
          <div class="inp">
            <span class="ikon"><svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72c.13.96.36 1.9.7 2.81a2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45c.9.34 1.85.57 2.81.7A2 2 0 0 1 22 16.92z"/></svg></span>
            <input id="no_hp" name="no_hp" type="tel" inputmode="numeric" placeholder="081234567890" value="{{ old('no_hp') }}" autocomplete="tel" required autofocus>
          </div>
          @if ($adaGalat)
            <div class="galat">{{ $errors->first() }}</div>
          @endif
          <button class="tombol" type="submit"><span id="lbl">Masuk Rapat</span>
            <svg id="ar" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14M13 6l6 6-6 6"/></svg>
          </button>
        </form>

        <div class="ket">
          <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><path d="M12 16v-4M12 8h.01"/></svg>
          <div>Nama Anda di Zoom terisi otomatis sesuai jabatan. Mohon tidak mengubahnya.</div>
        </div>
      </div>
    @else
      <div class="tutup">Rapat ini belum dibuka atau sudah ditutup.</div>
    @endif
    <div class="kaki"><b>musyawarahpengurus.web.id</b></div>
  </div>

</div>

<script>
function ripple(btn,e){var r=document.createElement('span');r.className='ripple';var d=Math.max(btn.offsetWidth,btn.offsetHeight);var rect=btn.getBoundingClientRect();r.style.width=r.style.height=d+'px';r.style.left=((e.clientX||rect.left+rect.width/2)-rect.left-d/2)+'px';r.style.top=((e.clientY||rect.top+rect.height/2)-rect.top-d/2)+'px';btn.appendChild(r);setTimeout(function(){r.remove()},600);}
function keForm(btn,e){ripple(btn,e);document.getElementById('s1').classList.add('keluar-kiri');var s2=document.getElementById('s2');setTimeout(function(){s2.classList.remove('masuk-kanan');var i=document.getElementById('no_hp');if(i)i.focus();},140);}
function kirim(form){var lbl=document.getElementById('lbl'),ar=document.getElementById('ar');if(ar)ar.style.display='none';if(lbl)lbl.innerHTML='<span class="spin"></span>';return true;}
</script>
</body>
</html>
