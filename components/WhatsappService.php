<?php

namespace app\components;

use Yii;
use yii\base\Component;

class WhatsappService extends Component
{
    private $apiUrl = 'https://graph.facebook.com/v17.0/';
    private $phoneNumberId;
    private $accessToken;

    public function init()
    {
        parent::init();
        $this->phoneNumberId = Yii::$app->params['whatsapp']['phoneNumberId'];
        $this->accessToken = Yii::$app->params['whatsapp']['accessToken'];
    }

    /**
     * Env\u00eda un mensaje de WhatsApp
     * @param string $to N\u00famero de tel\u00e9fono destino
     * @param string $message Mensaje a enviar
     * @return bool
     */
    public function sendMessage($to, $message)
    {
        $url = $this->apiUrl . $this->phoneNumberId . '/messages';
        
        $data = [
            'messaging_product' => 'whatsapp',
            'to' => $to,
            'type' => 'text',
            'text' => [
                'body' => $message
            ]
        ];

        $headers = [
            'Authorization: Bearer ' . $this->accessToken,
            'Content-Type: application/json'
        ];

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        return $httpCode >= 200 && $httpCode < 300;
    }

    /**
     * Env\u00eda una notificaci\u00f3n de geocerca con la lista de veh\u00edculos
     * @param string $to N\u00famero de tel\u00e9fono destino
     * @param string $vehiculoSaliente Placa del veh\u00edculo que sali\u00f3
     * @param string $geocerca Nombre de la geocerca
     * @param array $vehiculosRestantes Lista de veh\u00edculos que permanecen en la geocerca
     * @return bool
     */
    public function sendGeofenceNotification($to, $vehiculoSaliente, $geocerca, $vehiculosRestantes)
    {
        $message = "\u26A0\uFE0F El veh\u00edculo {$vehiculoSaliente} ha salido de la geocerca {$geocerca}\n\n";
        
        if (!empty($vehiculosRestantes)) {
            $message .= "\ud83d\ude97 Veh\u00edculos que permanecen en la geocerca:\n";
            foreach ($vehiculosRestantes as $vehiculo) {
                $message .= "- {$vehiculo}\n";
            }
        } else {
            $message .= "\u2139\uFE0F No hay veh\u00edculos dentro de la geocerca.";
        }

        return $this->sendMessage($to, $message);
    }
}