<?php

namespace app\controllers;

class KecamatanController extends BaseWilayahCrudController
{
    protected function wilayahLevel(): int
    {
        return 4;
    }

    protected function pageTitle(): string
    {
        return 'Master Data Kecamatan';
    }

    protected function activeMenu(): string
    {
        return 'kecamatan';
    }

    protected function parentLevel(): ?int
    {
        return 3;
    }

    protected function parentLabel(): string
    {
        return 'Kabupaten/Kota';
    }
}
