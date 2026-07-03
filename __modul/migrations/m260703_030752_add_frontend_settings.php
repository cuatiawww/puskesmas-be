<?php

use yii\db\Migration;

class m260703_030752_add_frontend_settings extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->execute("
            INSERT INTO public.system_setting (key, value, label, type, category) VALUES
            ('frontend_login_background', '/pkk.png', 'Background Login Page (Dashboard)', 'image', 'frontend'),
            ('frontend_register_background', '/pkk.png', 'Background Register Page (Dashboard)', 'image', 'frontend'),
            ('frontend_login_logo', '/Logo-Kemenkes.png', 'Logo Page (Dashboard)', 'image', 'frontend'),
            ('frontend_app_title', 'Indikator Penilaian Kinerja Puskesmas', 'Judul Utama (Dashboard)', 'text', 'frontend'),
            ('frontend_app_subtitle', 'Sistem pemantauan terpadu untuk melihat capaian, sebaran, dan perkembangan Puskesmas di seluruh wilayah Indonesia.', 'Deskripsi Utama (Dashboard)', 'text', 'frontend'),
            ('frontend_login_card_title', 'Asistensi Kinerja Puskesmas', 'Judul Card Login (Dashboard)', 'text', 'frontend'),
            ('frontend_login_card_subtitle', 'Silakan masuk untuk mengakses data kinerja Puskesmas.', 'Sub-judul Card Login (Dashboard)', 'text', 'frontend'),
            ('frontend_footer_text', '© 2026 Kementerian Kesehatan Republik Indonesia', 'Teks Hak Cipta/Footer (Dashboard)', 'text', 'frontend'),
            ('frontend_login_note', 'Akses terbatas untuk pengguna yang berwenang. Hubungi admin jika mengalami kendala masuk.', 'Catatan Login (Dashboard)', 'text', 'frontend');
        ");
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->execute("
            DELETE FROM public.system_setting WHERE category = 'frontend';
        ");
    }
}
