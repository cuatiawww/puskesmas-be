<?php

use yii\db\Migration;

class m260703_034323_add_wysiwyg_settings extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->execute("
            INSERT INTO public.system_setting (key, value, label, type, category) VALUES
            ('frontend_technical_guidelines', '<h5>Petunjuk Penggunaan Sistem</h5><p>Berikut adalah langkah-langkah untuk masuk ke sistem:</p><ol><li>Gunakan username dan password yang telah didaftarkan dan disetujui oleh admin.</li><li>Isi kode verifikasi captcha yang muncul dengan benar.</li><li>Jika Anda belum memiliki akun, silakan klik tautan <strong>Daftar sebagai Masyarakat</strong> di bawah tombol masuk.</li><li>Jika Anda mengalami kendala login, silakan hubungi unit administrator pusat melalui email bantuan resmi.</li></ol>', 'Petunjuk Teknis Penggunaan (Dashboard)', 'wysiwyg', 'frontend'),
            ('frontend_terms_conditions', '<p>Selamat datang di <strong>Asistensi Kinerja Puskesmas</strong>. Dengan mendaftar dan menggunakan sistem ini, Anda setuju untuk mematuhi ketentuan di bawah ini:</p><h5 class=\"font-bold text-slate-800 mt-3 text-sm\">1. Hak Akses & Akun</h5><p>Penggunaan akun ini terbatas untuk tujuan penelitian, riset, pemantauan nasional, dan keperluan kedinasan/lintas sektor resmi. Anda bertanggung jawab penuh atas kerahasiaan kata sandi dan aktivitas akun Anda.</p><h5 class=\"font-bold text-slate-800 mt-3 text-sm\">2. Penggunaan Data</h5><p>Data sarana fasilitas kesehatan, wilayah BPS, dan laporan bencana yang diperoleh melalui sistem ini hanya boleh digunakan sesuai tujuan akses yang diajukan. Dilarang menyebarkan, memanipulasi, atau menyalahgunakan data.</p><h5 class=\"font-bold text-slate-800 mt-3 text-sm\">3. Verifikasi & Validasi</h5><p>Pendaftaran akun Anda memerlukan verifikasi email melalui OTP dan persetujuan manual oleh Administrator Pusat.</p><h5 class=\"font-bold text-slate-800 mt-3 text-sm\">4. Keamanan Sistem</h5><p>Sistem ini dilindungi oleh mekanisme keamanan berlapis. Setiap percobaan akses ilegal akan diproses sesuai hukum yang berlaku.</p>', 'Syarat & Ketentuan Pengguna (Dashboard)', 'wysiwyg', 'frontend');
        ");
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->execute("
            DELETE FROM public.system_setting WHERE key IN ('frontend_technical_guidelines', 'frontend_terms_conditions');
        ");
    }
}
