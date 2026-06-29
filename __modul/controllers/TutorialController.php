<?php

namespace app\controllers;

use yii\web\Controller;

class TutorialController extends BaseController
{
    public function actionIndex()
    {
        return $this->render('index');
    }
}
