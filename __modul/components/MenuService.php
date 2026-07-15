<?php
namespace app\components;

use Yii;
use yii\db\Exception;

class MenuService
{
    /**
     * Ambil struktur menu (modul -> sub_modul parent -> children)
     * @return array
     */
    public static function getMenu(): array
    {
        try {
            $user = Yii::$app->user;
            $db = Yii::$app->get('db_user');

            $levelName = (string)($user->identity->level_user_id ?? '');
            $isSuperAdmin = (stripos($levelName, '1') !== false);

            if ($isSuperAdmin) {
                // Super Admin: semua modul + parent sub_modul (parent_id NULL)
                $results = $db->createCommand("
                    SELECT
                        m.id as modul_id,
                        m.nama_modul,
                        m.label as modul_label,
                        m.deskripsi as modul_deskripsi,
                        m.urutan as modul_urutan,
                        sm.id as sub_modul_id,
                        sm.nama_sub_modul,
                        sm.label as sub_modul_label,
                        sm.route,
                        sm.icon,
                        sm.urutan as sub_modul_urutan,
                        sm.parent_id
                    FROM modul m
                    LEFT JOIN sub_modul sm
                        ON m.id = sm.modul_id
                        AND sm.is_active = true
                        AND sm.parent_id IS NULL
                    WHERE m.is_active = true
                    ORDER BY m.urutan ASC, sm.urutan ASC
                ")->queryAll();

                // Semua children (parent_id NOT NULL)
                $children = $db->createCommand("
                    SELECT
                        id,
                        nama_sub_modul,
                        label,
                        route,
                        icon,
                        urutan,
                        parent_id
                    FROM sub_modul
                    WHERE is_active = true AND parent_id IS NOT NULL
                    ORDER BY urutan ASC
                ")->queryAll();

            } else {
                $levelUserId = (int)($user->identity->level_user_id ?? 0);

                // Parent sub_modul yang punya hak view
                $results = $db->createCommand("
                    SELECT DISTINCT
                        m.id as modul_id,
                        m.nama_modul,
                        m.label as modul_label,
                        m.deskripsi as modul_deskripsi,
                        m.urutan as modul_urutan,
                        sm.id as sub_modul_id,
                        sm.nama_sub_modul,
                        sm.label as sub_modul_label,
                        sm.route,
                        sm.icon,
                        sm.urutan as sub_modul_urutan,
                        sm.parent_id
                    FROM modul m
                    INNER JOIN sub_modul sm
                        ON m.id = sm.modul_id
                        AND sm.is_active = true
                        AND sm.parent_id IS NULL
                    INNER JOIN hak_akses ha
                        ON sm.id = ha.sub_modul_id
                    WHERE m.is_active = true
                        AND ha.level_user_id = :level_user_id
                        AND ha.can_view = true
                    ORDER BY m.urutan ASC, sm.urutan ASC
                ", [':level_user_id' => $levelUserId])->queryAll();

                // Children yang punya hak view
                $children = $db->createCommand("
                    SELECT DISTINCT
                        sm.id,
                        sm.nama_sub_modul,
                        sm.label,
                        sm.route,
                        sm.icon,
                        sm.urutan,
                        sm.parent_id
                    FROM sub_modul sm
                    INNER JOIN hak_akses ha
                        ON sm.id = ha.sub_modul_id
                    WHERE sm.is_active = true
                        AND sm.parent_id IS NOT NULL
                        AND ha.level_user_id = :level_user_id
                        AND ha.can_view = true
                    ORDER BY sm.urutan ASC
                ", [':level_user_id' => $levelUserId])->queryAll();

                // Ambil parent dari children (kalau user cuma punya akses ke child)
                $parentsFromChildren = $db->createCommand("
                    SELECT DISTINCT
                        m.id as modul_id,
                        m.nama_modul,
                        m.label as modul_label,
                        m.deskripsi as modul_deskripsi,
                        m.urutan as modul_urutan,
                        parent_sm.id as sub_modul_id,
                        parent_sm.nama_sub_modul,
                        parent_sm.label as sub_modul_label,
                        parent_sm.route,
                        parent_sm.icon,
                        parent_sm.urutan as sub_modul_urutan,
                        parent_sm.parent_id
                    FROM sub_modul child_sm
                    INNER JOIN hak_akses ha
                        ON child_sm.id = ha.sub_modul_id
                    INNER JOIN sub_modul parent_sm
                        ON child_sm.parent_id = parent_sm.id
                    INNER JOIN modul m
                        ON parent_sm.modul_id = m.id
                    WHERE child_sm.is_active = true
                        AND parent_sm.is_active = true
                        AND m.is_active = true
                        AND child_sm.parent_id IS NOT NULL
                        AND parent_sm.parent_id IS NULL
                        AND ha.level_user_id = :level_user_id
                        AND ha.can_view = true
                ", [':level_user_id' => $levelUserId])->queryAll();

                // Merge + unique (modul_id + sub_modul_id)
                $merged = array_merge($results, $parentsFromChildren);
                $unique = [];
                $seen = [];
                foreach ($merged as $row) {
                    $key = $row['modul_id'] . '_' . $row['sub_modul_id'];
                    if (!isset($seen[$key])) {
                        $unique[] = $row;
                        $seen[$key] = true;
                    }
                }
                $results = $unique;
            }

            // Group children by parent_id and normalize their route values
            $childrenByParent = [];
            foreach ($children as $child) {
                $pid = $child['parent_id'];
                if (!isset($childrenByParent[$pid])) $childrenByParent[$pid] = [];
                // normalize route for child
                $child['route'] = self::normalizeRoute($child['route'] ?? '');
                $childrenByParent[$pid][] = $child;
            }

            // Group by modul
            $menu = [];
            foreach ($results as $row) {
                $modulId = $row['modul_id'];

                if (!isset($menu[$modulId])) {
                    $menu[$modulId] = [
                        'id' => $modulId,
                        'nama_modul' => $row['nama_modul'],
                        'label' => $row['modul_label'],
                        'deskripsi' => $row['modul_deskripsi'],
                        'urutan' => $row['modul_urutan'],
                        'sub_modules' => []
                    ];
                }

                if (!empty($row['sub_modul_id'])) {
                    $menu[$modulId]['sub_modules'][] = [
                        'id' => $row['sub_modul_id'],
                        'nama_sub_modul' => $row['nama_sub_modul'],
                        'label' => $row['sub_modul_label'],
                        // normalize route (convert 'data-icd-x.php' -> '/data-icd-x')
                        'route' => self::normalizeRoute($row['route'] ?? ''),
                        'icon' => $row['icon'],
                        'urutan' => $row['sub_modul_urutan'],
                        'children' => $childrenByParent[$row['sub_modul_id']] ?? [],
                    ];
                }
            }

            return $menu;

        } catch (Exception $e) {
            Yii::error("MenuService DB error: " . $e->getMessage(), __METHOD__);
            return [];
        }
    }

    /**
     * Normalize route values from DB so menu links use pretty URLs.
     * Examples:
     *  - "data-icd-x.php" => "/data-icd-x"
     *  - "data-jamaah" => "/data-jamaah"
     *  - "http://..." unchanged
     *  - "/profil" => "/profil"
     *  - "/puskesmas/profil" on localhost project folder => "/profil"
     */
    private static function normalizeRoute(string $route): string
    {
        $r = trim((string)$route);
        if ($r === '') return '#!';
        // Leave absolute URLs as-is
        if (stripos($r, 'http://') === 0 || stripos($r, 'https://') === 0) return $r;
        // If contains .php suffix, remove it and use pretty path
        if (preg_match('/\.php$/i', $r)) {
            $r = preg_replace('/\.php$/i', '', $r);
            if ($r === '') return '#!';
            $r = (strpos($r, '/') === 0) ? $r : '/' . ltrim($r, '/');
        }
        // If looks like query param style (index.php?r=...), keep as-is
        if (stripos($r, 'index.php') !== false || stripos($r, 'r=') !== false) return $r;
        // Ensure leading slash for internal routes
        $r = (strpos($r, '/') === 0) ? $r : '/' . ltrim($r, '/');

        // Keep only the Yii route here. Url::to() will add baseUrl once when the menu is rendered.
        // This also fixes links such as /puskesmas/puskesmas/user-registration/index.
        $base = Yii::$app->params['base_url'] ?? '';
        if (!empty($base)) {
            $base = rtrim($base, '/');
            // Only strip if the base URL prefix is repeated, e.g. /puskesmas/puskesmas/index
            if ($base !== '' && strpos($r, $base . $base . '/') === 0) {
                $r = substr($r, strlen($base));
            }
        }

        return $r;
    }
}
