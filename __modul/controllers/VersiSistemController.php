<?php

namespace app\controllers;

use yii\web\Controller;

class VersiSistemController extends BaseController
{
    public function actionIndex()
    {
        return $this->render('index');
    }
}
