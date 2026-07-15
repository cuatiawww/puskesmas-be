<?php

namespace app\controllers;

class DesaController extends BaseWilayahCrudController
{
    protected function wilayahLevel(): int
    {
        return 5;
    }

    protected function pageTitle(): string
    {
        return 'Master Data Desa/Kelurahan';
    }

    protected function activeMenu(): string
    {
        return 'desa';
    }

    protected function parentLevel(): ?int
    {
        return 4;
    }

    protected function parentLabel(): string
    {
        return 'Kecamatan';
    }
}
