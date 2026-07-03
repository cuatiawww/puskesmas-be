<?php

use yii\db\Migration;

class m260703_020906_add_system_setting_menu extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $parent = $this->db->createCommand("SELECT id FROM public.sub_modul WHERE nama_sub_modul = 'konfigurasi' LIMIT 1")->queryOne();
        if ($parent) {
            $parentId = (int)$parent['id'];
            
            // Insert submodule using SQL DDL
            $this->execute("
                INSERT INTO public.sub_modul (modul_id, nama_sub_modul, label, route, icon, urutan, is_active, parent_id)
                VALUES (3, 'system-setting', 'KONFIGURASI SISTEM', '/system-setting/index', 'ph-duotone ph-cogs', 4, true, {$parentId});
            ");
            
            // Get the inserted sub_modul ID
            $inserted = $this->db->createCommand("SELECT id FROM public.sub_modul WHERE nama_sub_modul = 'system-setting' AND parent_id = {$parentId} LIMIT 1")->queryOne();
            if ($inserted) {
                $subModulId = (int)$inserted['id'];
                
                // Insert hak_akses for level 1 (Super Admin)
                $this->execute("
                    INSERT INTO public.hak_akses (level_user_id, sub_modul_id, can_view, can_create, can_update, can_delete)
                    VALUES (1, {$subModulId}, true, true, true, true);
                ");
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $inserted = $this->db->createCommand("SELECT id FROM public.sub_modul WHERE nama_sub_modul = 'system-setting' LIMIT 1")->queryOne();
        if ($inserted) {
            $subModulId = (int)$inserted['id'];
            $this->execute("DELETE FROM public.hak_akses WHERE sub_modul_id = {$subModulId};");
            $this->execute("DELETE FROM public.sub_modul WHERE id = {$subModulId};");
        }
    }
}
