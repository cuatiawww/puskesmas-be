<?php

namespace app\controllers;

use app\components\TimeHelper;
use Yii;
use app\models\UserActivityLog;
use yii\data\ActiveDataProvider;
use yii\data\SqlDataProvider;
use yii\web\NotFoundHttpException;
use yii\web\ForbiddenHttpException;

class UserActivityController extends BaseController
{
    /**
     * Enforce Super Admin authorization
     */
    public function beforeAction($action)
    {
        if (!parent::beforeAction($action)) {
            return false;
        }

        // Restrict this module strictly to Super Admins (Level User ID = 1)
        if (!$this->isCurrentUserSuperAdmin()) {
            throw new ForbiddenHttpException('Anda tidak memiliki izin untuk mengakses halaman Log Aktivitas.');
        }

        return true;
    }

    /**
     * Dashboard: User activity matrix & statistical metrics
     */
    public function actionIndex()
    {
        $db = Yii::$app->get('db_user');

        // 1. Fetch statistical widgets
        $stats = [
            'total_logs' => (int) UserActivityLog::find()->count(),
            'active_today' => (int) UserActivityLog::find()
                ->select('username')
                ->where(['>=', 'created_at', TimeHelper::todayStart()])
                ->andWhere(['<', 'created_at', TimeHelper::tomorrowStart()])
                ->distinct()
                ->count(),
            'create_actions' => (int) UserActivityLog::find()->where(['action' => 'create'])->count(),
            'update_actions' => (int) UserActivityLog::find()->where(['action' => 'update'])->count(),
            'delete_actions' => (int) UserActivityLog::find()->where(['action' => 'delete'])->count(),
        ];

        // 2. Fetch User Matrix (aggregrated log counts per user)
        $searchModel = new \yii\base\DynamicModel(['username']);
        $searchModel->addRule(['username'], 'string');
        $searchModel->load(Yii::$app->request->queryParams);

        $whereClause = '1=1';
        $params = [];
        if (!empty($searchModel->username)) {
            $whereClause = 'username ILIKE :username';
            $params[':username'] = '%' . $searchModel->username . '%';
        }

        // Count total unique users for pagination
        $totalCount = $db->createCommand("
            SELECT COUNT(DISTINCT username) 
            FROM user_activity_log 
            WHERE {$whereClause}
        ", $params)->queryScalar();

        // Data provider for aggregated matrix
        $dataProvider = new SqlDataProvider([
            'db' => $db,
            'sql' => "
                SELECT 
                    username,
                    COUNT(CASE WHEN action = 'view' THEN 1 END) as view_count,
                    COUNT(CASE WHEN action = 'create' THEN 1 END) as create_count,
                    COUNT(CASE WHEN action = 'update' THEN 1 END) as update_count,
                    COUNT(CASE WHEN action = 'delete' THEN 1 END) as delete_count,
                    COUNT(*) as total_count,
                    MAX(created_at) as last_active,
                    (SELECT ip_address FROM user_activity_log u2 WHERE u2.username = user_activity_log.username ORDER BY created_at DESC LIMIT 1) as last_ip,
                    (SELECT browser FROM user_activity_log u3 WHERE u3.username = user_activity_log.username ORDER BY created_at DESC LIMIT 1) as last_browser,
                    (SELECT platform FROM user_activity_log u4 WHERE u4.username = user_activity_log.username ORDER BY created_at DESC LIMIT 1) as last_platform
                FROM user_activity_log
                WHERE {$whereClause}
                GROUP BY username
            ",
            'params' => $params,
            'totalCount' => $totalCount,
            'pagination' => [
                'pageSize' => 20,
            ],
            'sort' => [
                'attributes' => ['username', 'total_count', 'last_active'],
                'defaultOrder' => ['last_active' => SORT_DESC],
            ],
        ]);

        return $this->render('index', [
            'dataProvider' => $dataProvider,
            'searchModel' => $searchModel,
            'stats' => $stats,
        ]);
    }

    /**
     * User profile log timeline view
     */
    public function actionView($username)
    {
        $searchModel = new \yii\base\DynamicModel(['action', 'route']);
        $searchModel->addRule(['action', 'route'], 'string');
        $searchModel->load(Yii::$app->request->queryParams);

        $query = UserActivityLog::find()->where(['username' => $username]);

        if (!empty($searchModel->action)) {
            $query->andWhere(['action' => $searchModel->action]);
        }
        if (!empty($searchModel->route)) {
            $query->andWhere(['like', 'route', $searchModel->route]);
        }

        $dataProvider = new ActiveDataProvider([
            'query' => $query->orderBy(['created_at' => SORT_DESC]),
            'pagination' => [
                'pageSize' => 25,
            ],
        ]);

        return $this->render('view', [
            'username' => $username,
            'dataProvider' => $dataProvider,
            'searchModel' => $searchModel,
        ]);
    }

    /**
     * Detail popup / page of a single activity log, including changes visualization
     */
    public function actionDetail($id)
    {
        $model = UserActivityLog::findOne($id);
        if ($model === null) {
            throw new NotFoundHttpException('Detail log tidak ditemukan.');
        }

        return $this->render('detail', [
            'model' => $model,
        ]);
    }
}
