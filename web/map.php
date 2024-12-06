<?php

function base64UrlEncode($data) {
    return str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($data));
}

function generateAccessToken($serviceAccountFile) {
    $serviceAccount = json_decode(file_get_contents($serviceAccountFile), true);

    $now = time();
    $expires = $now + 3600; // Token valid for 1 hour

    $header = [
        'alg' => 'RS256',
        'typ' => 'JWT'
    ];

    $payload = [
        'iss' => $serviceAccount['client_email'],
        'sub' => $serviceAccount['client_email'],
        'aud' => 'https://oauth2.googleapis.com/token',
        'iat' => $now,
        'exp' => $expires,
        'scope' => 'https://www.googleapis.com/auth/firebase.messaging'
    ];

    $base64UrlHeader = base64UrlEncode(json_encode($header));
    $base64UrlPayload = base64UrlEncode(json_encode($payload));

    $dataToSign = $base64UrlHeader . "." . $base64UrlPayload;

    // Load the private key
    $privateKey = openssl_pkey_get_private($serviceAccount['private_key']);
    if (!$privateKey) {
        die('Error loading private key');
    }

    // Sign the data
    $signature = '';
    openssl_sign($dataToSign, $signature, $privateKey, 'sha256');
    openssl_free_key($privateKey);

    $base64UrlSignature = base64UrlEncode($signature);

    $jwt = $base64UrlHeader . "." . $base64UrlPayload . "." . $base64UrlSignature;

    $postFields = http_build_query([
        'grant_type' => 'urn:ietf:params:oauth:grant-type:jwt-bearer',
        'assertion' => $jwt
    ]);

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, 'https://oauth2.googleapis.com/token');
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/x-www-form-urlencoded']);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $postFields);
    $response = curl_exec($ch);
    curl_close($ch);

    if ($response === false) {
        die('Error en la solicitud CURL: ' . curl_error($ch));
    }

    $data = json_decode($response, true);

    if (isset($data['access_token'])) {
        return $data['access_token'];
    } else {
        die('Error en la respuesta de la solicitud de token: ' . $response);
    }
}

// Título y mensaje fijos
$titulo = "¡CONTINGENCIA! 🚧";
$mensaje = "Dirigete a la contingencia en cuanto antes 🏃";

$notification = [
    'title' => $titulo,
    'body' => $mensaje,
];

$extraNotificationData = ["moredata" => 'dd'];

// Consultar la base de datos para obtener los tokens de la tabla de usuario con el campo api_key
$mysqli = new mysqli("capasu.ddns.net", "sqlcapasu", "mysql-ui2018", "reportes_prueba");

if ($mysqli->connect_error) {
    die("Connection failed: " . $mysqli->connect_error);
}

// Consulta para verificar si hay una contingencia activa
$contingenciaSql = "SELECT COUNT(*) as count, MAX(id) as newest_id, colonia FROM contingencias WHERE activo = 1 GROUP BY colonia ORDER BY MAX(fecha_inicio) DESC LIMIT 1";
$contingenciaResult = $mysqli->query($contingenciaSql);

if ($contingenciaResult !== false) {
    $contingenciaRow = $contingenciaResult->fetch_assoc();
    $contingenciaCount = $contingenciaRow['count'];

    if ($contingenciaCount > 0) {
        // Si hay una contingencia activa, procede a enviar la notificación
        $sql = "SELECT api_key FROM usuario";
        $result = $mysqli->query($sql);

        if ($result !== false) {
            $accessToken = generateAccessToken('prueba-dff5d-firebase-adminsdk-pelhq-385d322171.json');

            while ($row = $result->fetch_assoc()) {
                $token = $row['api_key'];

                $fcmNotification = [
                    'message' => [
                        'token' => $token,
                        'notification' => $notification,
                        'data' => $extraNotificationData
                    ]
                ];

                $headers = [
                    'Authorization: Bearer ' . $accessToken,
                    'Content-Type: application/json'
                ];

                $ch = curl_init();
                curl_setopt($ch, CURLOPT_URL, 'https://fcm.googleapis.com/v1/projects/prueba-dff5d/messages:send');
                curl_setopt($ch, CURLOPT_POST, true);
                curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
                curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($fcmNotification));
                $curlResult = curl_exec($ch);
                curl_close($ch);
                echo $curlResult;
            }

            $result->close();
        } else {
            echo "Error en la consulta de usuarios: " . $mysqli->error;
        }
    } else {
        echo "No hay contingencia activa, no se envió la notificación.";
    }
} else {
    echo "Error en la consulta de contingencias: " . $mysqli->error;
}

$mysqli->close();
?>