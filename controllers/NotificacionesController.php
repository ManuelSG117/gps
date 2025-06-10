<?php

namespace app\controllers;

use Yii;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use app\models\Notificaciones;

class NotificacionesController extends Controller
{
    public function behaviors()
    {
        return [
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'delete' => ['POST'],
                ],
            ],
        ];
    }

    // Listado de todas las notificaciones
    public function actionIndex()
    {
        $notificaciones = Notificaciones::find()->orderBy(['fecha_creacion' => SORT_DESC])->all();
        return $this->render('index', [
            'notificaciones' => $notificaciones,
        ]);
    }

    // Obtener notificaciones recientes (para el dropdown)
    public function actionGetRecent()
    {
        \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        $notificaciones = Notificaciones::find()
            ->orderBy(['leido' => SORT_ASC, 'fecha_creacion' => SORT_DESC])
            ->limit(10)
            ->asArray()
            ->all();
        return $notificaciones;
    }

    // Marcar como leída
    public function actionMarkAsRead($id)
    {
        \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        $notificacion = Notificaciones::findOne($id);
        if ($notificacion) {
            $notificacion->leido = 1;
            $notificacion->fecha_lectura = date('Y-m-d H:i:s');
            $notificacion->save();
            return ['success' => true];
        }
        return ['success' => false];
    }

    // Eliminar notificación
    public function actionDelete($id)
    {
        \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        $notificacion = Notificaciones::findOne($id);
        if ($notificacion) {
            $notificacion->delete();
            return ['success' => true];
        }
        return ['success' => false];
    }

    // Marcar todas las notificaciones como leídas
    public function actionMarkAllAsRead()
    {
        \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        Notificaciones::updateAll(['leido' => 1, 'fecha_lectura' => date('Y-m-d H:i:s')], ['leido' => 0]);
        return ['success' => true];
    }
}