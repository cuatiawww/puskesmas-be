<?php

namespace app\controllers;

class PosyanduController extends BaseFaskesMasterController
{
    protected function facilityConfig(): array
    {
        return [
            'label' => 'Master Data Posyandu',
            'active_menu' => 'posyandu',
            'jenis' => 'posyandu',
            'jenis_label' => 'Posyandu',
        ];
    }
}
