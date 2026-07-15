<?php

namespace app\controllers;

class PustuController extends BaseFaskesMasterController
{
    protected function facilityConfig(): array
    {
        return [
            'label' => 'Master Data Pustu',
            'active_menu' => 'pustu',
            'jenis' => 'pustu',
            'jenis_label' => 'Pustu',
        ];
    }
}
