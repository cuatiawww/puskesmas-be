<?php

namespace app\controllers;

class BbkBblkController extends BaseFaskesMasterController
{
    protected function facilityConfig(): array
    {
        return [
            'label' => 'Master Data BBK/BBLK',
            'active_menu' => 'bbk-bblk',
            'jenis' => 'bkk',
            'jenis_label' => 'BBK/BBLK',
        ];
    }
}
