<?php

namespace app\controllers;

use app\components\TimeHelper;
use Yii;
use app\models\Beranda;
use yii\web\Response;

class BerandaController extends BaseController
{
    public $enableCsrfValidation = false;

    public function actionIndex()
    {
        try {
            return $this->render('index', [
                'stats'        => Beranda::getStats(),
                'totalCheckin' => 0,
                'lastCheckin'  => null,
                'currentDate'  => TimeHelper::date(),
                'currentTime'  => TimeHelper::hourMinute(),
            ]);
        } catch (\Exception $e) {
            Yii::error('Beranda Controller Error: ' . $e->getMessage());
            return $this->render('index', [
                'stats'        => Beranda::getStats(),
                'totalCheckin' => 0,
                'lastCheckin'  => null,
                'currentDate'  => TimeHelper::date(),
                'currentTime'  => TimeHelper::hourMinute(),
            ]);
        }
    }

    /**
     * Get statistics data via AJAX
     */
    public function actionGetStats()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $stats = Beranda::getStats();
        return [
            'success' => true,
            'data'    => $stats,
        ];
    }
}
