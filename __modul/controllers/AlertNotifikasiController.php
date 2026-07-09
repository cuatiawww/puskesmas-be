<?php

namespace app\controllers;

use Yii;
use app\models\UserRegistration;

class AlertNotifikasiController extends BaseController
{
    /**
     * Mark a notification as read and redirect to target URL
     */
    public function actionRead($id, $url = null)
    {
        Yii::$app->session->set('notif_read_' . $id, true);
        
        if (!empty($url) && $url !== '#') {
            return $this->redirect($url);
        }
        return $this->redirect(['index']);
    }

    /**
     * Renders the Alert & Notification index dashboard
     */
    public function actionIndex()
    {
        $isAdmin = (Yii::$app->user->identity && (int)(Yii::$app->user->identity->level_user_id ?? 0) === 1);
        $notifications = [];

        if ($isAdmin) {
            try {
                $pendingRegistrations = UserRegistration::find()
                    ->where(['status' => UserRegistration::STATUS_PENDING_APPROVAL])
                    ->orderBy(['id' => SORT_DESC])
                    ->all();
                    
                if (!empty($pendingRegistrations)) {
                    foreach ($pendingRegistrations as $reg) {
                        $id = 'reg_' . $reg->id;
                        $isRead = Yii::$app->session->get('notif_read_' . $id, false);
                        
                        $notifications[] = [
                            'id' => $id,
                            'title' => 'Persetujuan Akun Baru',
                            'description' => 'User ' . htmlspecialchars($reg->nama_lengkap) . ' (' . htmlspecialchars($reg->username) . ') mengajukan akses ' . htmlspecialchars($reg->kategori_akses) . '.',
                            'time' => Yii::$app->formatter->asRelativeTime($reg->created_at),
                            'badge' => 'Menunggu Approval',
                            'badge_class' => 'bg-light-info border border-info text-info',
                            'icon' => 'ph-duotone ph-user-circle-plus text-info',
                            'category' => 'User Approval',
                            'status' => $isRead ? 'Dibaca' : 'Belum Dibaca',
                            'url' => \yii\helpers\Url::to(['/user-registration/view', 'id' => $reg->id]),
                        ];
                    }
                }
            } catch (\Throwable $e) {
                // Fallback silently if DB/table issue
            }
        }

        // Summarize stats
        $totalCount = count($notifications);
        $unreadCount = 0;
        foreach ($notifications as $n) {
            if ($n['status'] === 'Belum Dibaca') {
                $unreadCount++;
            }
        }
        $readCount = $totalCount - $unreadCount;

        return $this->render('index', [
            'notifications' => $notifications,
            'stats' => [
                'total' => $totalCount,
                'unread' => $unreadCount,
                'read' => $readCount,
            ]
        ]);
    }
}
