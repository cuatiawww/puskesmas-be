<?php
namespace app\commands;

use Yii;
use yii\console\Controller;
use yii\console\ExitCode;

/**
 * Simple console helper to add menu/sub_modul and hak_akses entries for Data Jamaah
 * Usage (from project root):
 *   php yii menu-install/add-data-jamaah --parent="Master Data" --levels=1,2
 * Options:
 *   --parent  Name of parent modul (exact match) or modul id
 *   --levels  Comma separated level_user_id values to grant view access (default: 1)
 *
 * The command is idempotent: it will not duplicate entries if route/name already exists.
 */
class MenuInstallController extends Controller
{
    public function options($actionID)
    {
        return ['parent', 'levels', 'dryRun'];
    }

    public function actionAddDataJamaah($parent = 'Master Data', $levels = '1', $dryRun = false)
    {
        $db = Yii::$app->db;

        // resolve parent modul id
        if (is_numeric($parent)) {
            $modulId = (int)$parent;
            $modul = $db->createCommand('SELECT id, nama_modul FROM modul WHERE id = :id', [':id' => $modulId])->queryOne();
        } else {
            $modul = $db->createCommand('SELECT id, nama_modul FROM modul WHERE nama_modul = :name', [':name' => $parent])->queryOne();
        }

        if (!$modul) {
            $this->stderr("Parent modul not found: {$parent}\n");
            return ExitCode::UNSPECIFIED_ERROR;
        }

        $modulId = (int)$modul['id'];

        // check existing sub_modul with route 'data-jamaah'
        $existing = $db->createCommand('SELECT * FROM sub_modul WHERE route = :route', [':route' => 'data-jamaah'])->queryOne();
        if ($existing) {
            $this->stdout("Sub module 'data-jamaah' already exists (id={$existing['id']}).\n");
            $subModulId = (int)$existing['id'];
        } else {
            // compute next urutan
            $maxOrder = $db->createCommand('SELECT COALESCE(MAX(urutan),0) FROM sub_modul WHERE modul_id = :mid', [':mid' => $modulId])->queryScalar();
            $urutan = (int)$maxOrder + 1;

            $sql = 'INSERT INTO sub_modul (modul_id, nama_sub_modul, label, route, icon, urutan, is_active, parent_id) VALUES (:mid, :name, :label, :route, :icon, :urutan, true, NULL) RETURNING id';

            if ($dryRun) {
                $this->stdout("DRY RUN SQL: $sql with params mid={$modulId}, name='Data Jamaah' route='data-jamaah'\n");
                $subModulId = null;
            } else {
                $res = $db->createCommand($sql, [
                    ':mid' => $modulId,
                    ':name' => 'Data Jamaah',
                    ':label' => 'Data Jamaah',
                    ':route' => 'data-jamaah',
                    ':icon' => 'fa fa-users',
                    ':urutan' => $urutan,
                ])->queryOne();

                $subModulId = (int)($res['id'] ?? 0);
                $this->stdout("Inserted sub_modul 'Data Jamaah' with id={$subModulId}\n");
            }
        }

        // prepare hak_akses entries for provided levels
        $levelList = array_filter(array_map('trim', explode(',', $levels)));
        if (empty($levelList)) $levelList = ['1'];

        foreach ($levelList as $lvl) {
            $lvl = (int)$lvl;
            if ($lvl <= 0) continue;

            if ($subModulId === null) {
                $this->stdout("Skipping hak_akses insert for level {$lvl} because dryRun or existing sub_modul not resolved.\n");
                continue;
            }

            $existsHa = $db->createCommand('SELECT * FROM hak_akses WHERE level_user_id = :lid AND sub_modul_id = :smid', [':lid' => $lvl, ':smid' => $subModulId])->queryOne();
            if ($existsHa) {
                $this->stdout("hak_akses already exists for level {$lvl} and sub_modul {$subModulId}.\n");
                continue;
            }

            // default permissions: level 1 (admin) full access, others view only
            $canView = ($lvl === 1) ? true : true;
            $canCreate = ($lvl === 1) ? true : false;
            $canUpdate = ($lvl === 1) ? true : false;
            $canDelete = ($lvl === 1) ? true : false;

            if ($dryRun) {
                $this->stdout("DRY RUN: INSERT hak_akses for level {$lvl} sub_modul={$subModulId} (view={$canView}, create={$canCreate}, update={$canUpdate}, delete={$canDelete})\n");
            } else {
                $db->createCommand()->insert('hak_akses', [
                    'level_user_id' => $lvl,
                    'sub_modul_id' => $subModulId,
                    'can_view' => $canView,
                    'can_create' => $canCreate,
                    'can_update' => $canUpdate,
                    'can_delete' => $canDelete,
                ])->execute();
                $this->stdout("Inserted hak_akses for level {$lvl}.\n");
            }
        }

        $this->stdout("Done. Please verify the menu in the UI and adjust hak_akses if needed.\n");
        return ExitCode::OK;
    }
}
