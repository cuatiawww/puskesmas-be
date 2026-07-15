<?php

namespace app\controllers;

class KlinikController extends BaseFaskesMasterController
{
    protected function facilityConfig(): array
    {
        return [
            'label' => 'Master Data Klinik',
            'active_menu' => 'klinik',
            'jenis' => 'klinik',
            'jenis_label' => 'Klinik',
        ];
    }
}
