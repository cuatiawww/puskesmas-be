<?php

namespace app\components;

use Yii;
use yii\base\BootstrapInterface;
use yii\base\Event;
use yii\web\Application;
use yii\db\ActiveRecord;
use app\models\UserActivityLog;

class ActivityLogger extends \yii\base\Component implements BootstrapInterface
{
    /**
     * Bootstrap method to register global event listeners
     */
    public function bootstrap($app)
    {
        // Only register log listeners if running a web application
        if ($app instanceof Application) {
            // Listen for controller actions (page views and GET requests)
            Event::on(\yii\base\Controller::class, \yii\base\Controller::EVENT_BEFORE_ACTION, [$this, 'logControllerAction']);
            
            // Listen for database changes (Insert, Update, Delete) on ActiveRecord
            Event::on(ActiveRecord::class, ActiveRecord::EVENT_AFTER_INSERT, [$this, 'logModelInsert']);
            Event::on(ActiveRecord::class, ActiveRecord::EVENT_AFTER_UPDATE, [$this, 'logModelUpdate']);
            Event::on(ActiveRecord::class, ActiveRecord::EVENT_AFTER_DELETE, [$this, 'logModelDelete']);
        }
    }

    /**
     * Parse User Agent to extract browser name and operating system/platform
     */
    public static function parseUserAgent($userAgent)
    {
        $browser = 'Unknown';
        $platform = 'Unknown';

        if (empty($userAgent)) {
            return [$browser, $platform];
        }

        // Platform parsing
        if (preg_match('/android/i', $userAgent)) {
            $platform = 'Android';
        } elseif (preg_match('/iphone|ipad|ipod/i', $userAgent)) {
            $platform = 'iOS';
        } elseif (preg_match('/win/i', $userAgent)) {
            $platform = 'Windows';
        } elseif (preg_match('/mac/i', $userAgent)) {
            $platform = 'macOS';
        } elseif (preg_match('/linux/i', $userAgent)) {
            $platform = 'Linux';
        }

        // Browser parsing
        if (preg_match('/chrome|crios/i', $userAgent) && !preg_match('/edge|edg/i', $userAgent) && !preg_match('/opr/i', $userAgent)) {
            $browser = 'Chrome';
        } elseif (preg_match('/firefox|fxios/i', $userAgent)) {
            $browser = 'Firefox';
        } elseif (preg_match('/safari/i', $userAgent) && !preg_match('/chrome|crios/i', $userAgent) && !preg_match('/edge|edg/i', $userAgent)) {
            $browser = 'Safari';
        } elseif (preg_match('/edge|edg/i', $userAgent)) {
            $browser = 'Edge';
        } elseif (preg_match('/opr/i', $userAgent)) {
            $browser = 'Opera';
        } elseif (preg_match('/msie|trident/i', $userAgent)) {
            $browser = 'Internet Explorer';
        }

        return [$browser, $platform];
    }

    /**
     * Get Client IP Address
     */
    public static function getClientIp()
    {
        $request = Yii::$app->request;
        return $request->userIP ?? $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
    }

    /**
     * Log controller page view actions (GET requests)
     */
    public function logControllerAction($event)
    {
        /** @var \yii\base\Action $action */
        $action = $event->action;
        $controller = $action->controller;
        
        // Exclude system modules and logger paths to avoid log pollution and loop
        if ($controller->module->id === 'debug' || $controller->module->id === 'gii' || $controller->id === 'user-activity') {
            return;
        }

        // Only log GET requests as view events
        if (!Yii::$app->request->isGet) {
            return;
        }

        try {
            $userAgent = Yii::$app->request->userAgent;
            list($browser, $platform) = self::parseUserAgent($userAgent);

            $log = new UserActivityLog();
            $log->user_id = Yii::$app->user->id ?? null;
            $log->username = Yii::$app->user->identity->username ?? 'Guest';
            $log->action = 'view';
            $log->module = $controller->module->id === Yii::$app->id ? null : $controller->module->id;
            $log->controller = $controller->id;
            $log->action_id = $action->id;
            $log->route = $controller->route;
            $log->url = Yii::$app->request->absoluteUrl;
            $log->ip_address = self::getClientIp();
            $log->user_agent = $userAgent;
            $log->browser = $browser;
            $log->platform = $platform;
            
            // Record query parameters if present
            $queryParams = Yii::$app->request->get();
            if (!empty($queryParams)) {
                // Remove sensitive params if any
                unset($queryParams['password'], $queryParams['_csrf']);
                if (!empty($queryParams)) {
                    $log->changes = json_encode($queryParams);
                }
            }

            $log->save(false);
        } catch (\Throwable $e) {
            Yii::error('Gagal mencatat log controller view: ' . $e->getMessage(), __METHOD__);
        }
    }

    /**
     * Log database INSERT (Create) operations
     */
    public function logModelInsert($event)
    {
        $model = $event->sender;
        if ($model instanceof UserActivityLog) {
            return;
        }

        // Filter out sensitive data from the log
        $attributes = $model->getAttributes();
        foreach ($attributes as $name => $value) {
            if (in_array(strtolower($name), ['password', 'auth_key', 'password_hash', 'password_reset_token', 'password_reset_otp', 'access_token', 'token'], true)) {
                $attributes[$name] = '[PROTECTED]';
            }
            if (is_resource($value)) {
                $attributes[$name] = '[Resource]';
            }
        }

        $this->logModelChange('create', $model, $attributes);
    }

    /**
     * Log database UPDATE (Edit) operations with differential comparison
     */
    public function logModelUpdate($event)
    {
        $model = $event->sender;
        if ($model instanceof UserActivityLog) {
            return;
        }

        $oldValues = [];
        $newValues = [];
        
        foreach ($event->changedAttributes as $name => $oldValue) {
            $newValue = $model->getAttribute($name);
            
            // Mask sensitive fields
            if (in_array(strtolower($name), ['password', 'auth_key', 'password_hash', 'password_reset_token', 'password_reset_otp', 'access_token', 'token'], true)) {
                $oldValue = '[PROTECTED]';
                $newValue = '[PROTECTED]';
            }

            if ($newValue !== $oldValue) {
                if (is_resource($newValue)) {
                    $newValue = '[Resource]';
                }
                if (is_resource($oldValue)) {
                    $oldValue = '[Resource]';
                }
                
                $oldValues[$name] = $oldValue;
                $newValues[$name] = $newValue;
            }
        }

        // Only log if attributes actually changed
        if (!empty($newValues)) {
            $this->logModelChange('update', $model, [
                'old' => $oldValues,
                'new' => $newValues
            ]);
        }
    }

    /**
     * Log database DELETE operations
     */
    public function logModelDelete($event)
    {
        $model = $event->sender;
        if ($model instanceof UserActivityLog) {
            return;
        }

        $attributes = $model->getAttributes();
        foreach ($attributes as $name => $value) {
            if (in_array(strtolower($name), ['password', 'auth_key', 'password_hash', 'password_reset_token', 'password_reset_otp', 'access_token', 'token'], true)) {
                $attributes[$name] = '[PROTECTED]';
            }
            if (is_resource($value)) {
                $attributes[$name] = '[Resource]';
            }
        }

        $this->logModelChange('delete', $model, $attributes);
    }

    /**
     * Internal method to persist changes to the UserActivityLog ActiveRecord
     */
    protected function logModelChange($actionType, $model, $details = null)
    {
        // Prevent writing logs if not in web app request (e.g. console tasks/migrations)
        if (!(Yii::$app instanceof Application)) {
            return;
        }

        try {
            $userAgent = Yii::$app->request->userAgent;
            list($browser, $platform) = self::parseUserAgent($userAgent);

            $log = new UserActivityLog();
            $log->user_id = Yii::$app->user->id ?? null;
            $log->username = Yii::$app->user->identity->username ?? 'Guest';
            $log->action = $actionType;
            $log->module = Yii::$app->controller->module->id === Yii::$app->id ? null : Yii::$app->controller->module->id;
            $log->controller = Yii::$app->controller->id ?? 'unknown';
            $log->action_id = Yii::$app->controller->action->id ?? 'unknown';
            $log->route = Yii::$app->controller->route ?? 'unknown';
            $log->url = Yii::$app->request->absoluteUrl;
            $log->ip_address = self::getClientIp();
            $log->user_agent = $userAgent;
            $log->browser = $browser;
            $log->platform = $platform;
            
            $log->target_model = get_class($model);
            
            // Get Primary Key values
            $pk = $model->getPrimaryKey();
            $log->target_id = is_array($pk) ? json_encode($pk) : (string) $pk;

            if ($details !== null) {
                $log->changes = json_encode($details);
            }

            $log->save(false);
        } catch (\Throwable $e) {
            Yii::error('Gagal mencatat log perubahan database: ' . $e->getMessage(), __METHOD__);
        }
    }
}
