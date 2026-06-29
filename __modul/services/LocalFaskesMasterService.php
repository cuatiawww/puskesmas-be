<?php

namespace app\services;

use Yii;
use yii\db\Query;

class LocalFaskesMasterService
{
    private const TABLES = [
        'rs' => 'tbl_rs_sarana',
        'puskesmas' => 'tbl_puskesmas_sarana',
        'klinik' => 'tbl_klinik_sarana',
        'posyandu' => 'tbl_posyandu_sarana',
        'pustu' => 'tbl_pustu_sarana',
    ];

    public function getFacilityTable(array $params): array
    {
        $jenis = (string)($params['jenis'] ?? '');
        $table = self::TABLES[$jenis] ?? null;

        if ($table === null) {
            return [
                'rows' => [],
                'pagination' => [
                    'total' => 0,
                    'page' => 1,
                    'per_page' => (int)($params['per_page'] ?? 10),
                ],
                'debug' => ['message' => 'Jenis faskes belum diaktifkan untuk master data lokal.'],
            ];
        }

        $page = max(1, (int)($params['page'] ?? 1));
        $perPage = max(1, (int)($params['per_page'] ?? 10));
        $search = trim((string)($params['search'] ?? ''));
        $kodeProvinsi = trim((string)($params['kode_provinsi'] ?? ''));
        $kodeKabkota = trim((string)($params['kode_kabkota'] ?? ''));

        $query = (new Query())
            ->from($table);

        if ($search !== '') {
            $query->andWhere([
                'or',
                ['ilike', 'nama', $search],
                ['ilike', 'kode_satusehat', $search],
                ['ilike', 'kode_sarana', $search],
            ]);
        }

        if ($kodeProvinsi !== '') {
            if (in_array($jenis, ['rs', 'puskesmas', 'klinik'], true)) {
                $query->andWhere(['kode_prop' => $kodeProvinsi]);
            } else {
                $query->andWhere(['kode_provinsi' => $kodeProvinsi]);
            }
        }

        if ($kodeKabkota !== '') {
            if (in_array($jenis, ['rs', 'puskesmas', 'klinik'], true)) {
                $query->andWhere(['kode_kab' => $kodeKabkota]);
            } else {
                $query->andWhere(['kode_kabkota' => $kodeKabkota]);
            }
        }

        $countQuery = clone $query;
        $total = (int)$countQuery->count('*', Yii::$app->db);

        $rows = $query
            ->orderBy(['id' => SORT_DESC])
            ->offset(($page - 1) * $perPage)
            ->limit($perPage)
            ->all(Yii::$app->db);

        return [
            'rows' => $rows,
            'pagination' => [
                'total' => $total,
                'page' => $page,
                'per_page' => $perPage,
            ],
            'debug' => [
                'source' => 'database',
                'table' => $table,
            ],
        ];
    }
}
