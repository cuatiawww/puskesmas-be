<?php

namespace app\controllers;

class KabupatenKotaController extends BaseWilayahCrudController
{
    protected function wilayahLevel(): int
    {
        return 3;
    }

    protected function pageTitle(): string
    {
        return 'Master Data Kabupaten/Kota';
    }

    protected function activeMenu(): string
    {
        return 'kabupaten-kota';
    }

    protected function parentLevel(): ?int
    {
        return 2;
    }

    protected function parentLabel(): string
    {
        return 'Provinsi';
    }
}
