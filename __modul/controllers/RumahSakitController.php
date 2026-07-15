<?php

namespace app\controllers;

class RumahSakitController extends BaseFaskesMasterController
{
    protected function facilityConfig(): array
    {
        return [
            'label' => 'Master Data Rumah Sakit',
            'active_menu' => 'rumah-sakit',
            'jenis' => 'rs',
            'jenis_label' => 'Rumah Sakit',
        ];
    }
}
