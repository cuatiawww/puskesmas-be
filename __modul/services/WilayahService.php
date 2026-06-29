<?php

namespace app\services;

use Yii;
use yii\db\Query;

class WilayahService
{
    public function getProvinsiOptions(): array
    {
        return $this->mapRows(
            (new Query())
                ->select(['code', 'name'])
                ->from('wilayah_provinsi')
                ->where(['not', ['code' => null]])
                ->orderBy(['name' => SORT_ASC])
                ->all(Yii::$app->db)
        );
    }

    public function getKabupatenOptions(string $provinceCode): array
    {
        return $this->mapRows(
            (new Query())
                ->select(['code', 'name'])
                ->from('wilayah_kabupaten')
                ->where(['parent_code' => $provinceCode])
                ->andWhere(['not', ['code' => null]])
                ->orderBy(['name' => SORT_ASC])
                ->all(Yii::$app->db)
        );
    }

    public function getKecamatanOptions(string $kabupatenCode): array
    {
        return $this->mapRows(
            (new Query())
                ->select(['code', 'name'])
                ->from('wilayah_kecamatan')
                ->where(['parent_code' => $kabupatenCode])
                ->andWhere(['not', ['code' => null]])
                ->orderBy(['name' => SORT_ASC])
                ->all(Yii::$app->db)
        );
    }

    public function getDesaOptions(string $kecamatanCode): array
    {
        return $this->mapRows(
            (new Query())
                ->select(['code', 'name'])
                ->from('wilayah_desa')
                ->where(['parent_code' => $kecamatanCode])
                ->andWhere(['not', ['code' => null]])
                ->orderBy(['name' => SORT_ASC])
                ->all(Yii::$app->db)
        );
    }

    public function isValidProvinsi(string $provinceCode): bool
    {
        return (new Query())
            ->from('wilayah_provinsi')
            ->where(['code' => $provinceCode])
            ->exists(Yii::$app->db);
    }

    public function isValidKabupaten(string $provinceCode, string $kabupatenCode): bool
    {
        return (new Query())
            ->from('wilayah_kabupaten')
            ->where([
                'code' => $kabupatenCode,
                'parent_code' => $provinceCode,
            ])
            ->exists(Yii::$app->db);
    }

    public function isValidKecamatan(string $kabupatenCode, string $kecamatanCode): bool
    {
        return (new Query())
            ->from('wilayah_kecamatan')
            ->where([
                'code' => $kecamatanCode,
                'parent_code' => $kabupatenCode,
            ])
            ->exists(Yii::$app->db);
    }

    public function isValidDesa(string $kecamatanCode, string $desaCode): bool
    {
        return (new Query())
            ->from('wilayah_desa')
            ->where([
                'code' => $desaCode,
                'parent_code' => $kecamatanCode,
            ])
            ->exists(Yii::$app->db);
    }

    public function findProvinsiName(?string $provinceCode): ?string
    {
        if ($provinceCode === null || $provinceCode === '') {
            return null;
        }

        $name = (new Query())
            ->select('name')
            ->from('wilayah_provinsi')
            ->where(['code' => $provinceCode])
            ->scalar(Yii::$app->db);

        return $name !== false ? (string)$name : null;
    }

    public function findKabupatenName(?string $kabupatenCode): ?string
    {
        if ($kabupatenCode === null || $kabupatenCode === '') {
            return null;
        }

        $name = (new Query())
            ->select('name')
            ->from('wilayah_kabupaten')
            ->where(['code' => $kabupatenCode])
            ->scalar(Yii::$app->db);

        return $name !== false ? (string)$name : null;
    }

    private function mapRows(array $rows): array
    {
        return array_map(static function (array $row): array {
            return [
                'id' => (string)$row['code'],
                'code' => (string)$row['code'],
                'name' => (string)$row['name'],
            ];
        }, $rows);
    }
}
