<?php

namespace app\controllers;

class PuskesmasController extends BaseFaskesMasterController
{
    protected function facilityConfig(): array
    {
        return [
            'label' => 'Master Data Puskesmas',
            'active_menu' => 'puskesmas',
            'jenis' => 'puskesmas',
            'jenis_label' => 'Puskesmas',
        ];
    }
}
