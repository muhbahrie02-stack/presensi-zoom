<?php

namespace Tests\Feature;

use App\Models\AkunZoom;
use App\Models\Bidang;
use App\Models\Jabatan;
use App\Models\Kehadiran;
use App\Models\Peserta;
use App\Models\Rapat;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AlurMasukTest extends TestCase
{
    use RefreshDatabase;

    private Rapat $rapat;
    private Bidang $bidang;
    private Jabatan $jabatan;

    protected function setUp(): void
    {
        parent::setUp();
        $akun = AkunZoom::create(['nama' => 'uji', 'sdk_key' => 'KEY', 'sdk_secret' => 'SECRET']);
        $this->bidang = Bidang::create(['kode' => '000', 'nama' => 'Biro KKK']);
        $this->jabatan = Jabatan::create(['nama' => 'Ketua']);
        $this->rapat = Rapat::create([
            'slug' => 'ujirapat', 'judul' => 'Rapat Uji', 'akun_zoom_id' => $akun->id,
            'meeting_number' => '81234567890', 'mulai' => now(), 'selesai' => now()->addHour(),
        ]);
    }

    public function test_nomor_terdaftar_langsung_ke_ruang_dengan_nama_sesuai_pola(): void
    {
        Peserta::create(['no_hp' => '081234567890', 'nama' => 'Fulan', 'bidang_id' => $this->bidang->id,
            'jabatan_id' => $this->jabatan->id, 'terverifikasi' => true]);

        $this->post('/r/ujirapat', ['no_hp' => '+62 812-3456-7890'])->assertRedirect('/r/ujirapat/ruang');

        $this->assertSame('000.Biro KKK | Ketua. Fulan', Kehadiran::first()->nama_tampilan);
        $this->get('/r/ujirapat/ruang')->assertOk()->assertSee('000.Biro KKK | Ketua. Fulan');
    }

    public function test_nomor_belum_terdaftar_diarahkan_mendaftar_lalu_masuk(): void
    {
        $this->post('/r/ujirapat', ['no_hp' => '085500001111'])->assertRedirect('/r/ujirapat/daftar');

        $this->post('/r/ujirapat/daftar', [
            'nama' => 'Dr. Ahmad Fulan, S.H.', 'bidang_id' => $this->bidang->id, 'jabatan_id' => $this->jabatan->id,
        ])->assertRedirect('/r/ujirapat/ruang');

        $p = Peserta::where('no_hp', '085500001111')->first();
        $this->assertSame('Ahmad Fulan', $p->nama);
        $this->assertFalse($p->terverifikasi);
        $this->assertSame('mandiri', $p->sumber);
    }

    public function test_ping_mencatat_masuk_dan_sesi_lama_gugur_saat_login_ulang(): void
    {
        Peserta::create(['no_hp' => '081234567890', 'nama' => 'Fulan', 'bidang_id' => $this->bidang->id,
            'jabatan_id' => $this->jabatan->id, 'terverifikasi' => true]);

        $this->post('/r/ujirapat', ['no_hp' => '081234567890']);
        $this->postJson('/r/ujirapat/ping', ['event' => 'masuk'])->assertOk();
        $this->assertNotNull(Kehadiran::first()->masuk_at);

        // Perangkat lain login dengan nomor yang sama: token berganti
        Kehadiran::first()->update(['token' => 'token-perangkat-lain']);
        $this->postJson('/r/ujirapat/ping', ['event' => 'detak'])->assertStatus(409);
    }

    public function test_nomor_tidak_valid_ditolak(): void
    {
        $this->post('/r/ujirapat', ['no_hp' => '12345'])->assertSessionHasErrors('no_hp');
    }
}
