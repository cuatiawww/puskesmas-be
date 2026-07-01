<?php

namespace app\controllers;

class ProvinsiController extends BaseWilayahCrudController
{
    protected function wilayahLevel(): int
    {
        return 2;
    }

    protected function pageTitle(): string
    {
        return 'Master Data Provinsi';
    }

    protected function activeMenu(): string
    {
        return 'provinsi';
    }
}
